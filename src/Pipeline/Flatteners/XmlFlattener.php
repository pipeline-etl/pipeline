<?php

/**
 * This file contains the XmlFlattener class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Flatteners;

use Exception;
use Pipeline\Common\FlattenerInterface;
use Pipeline\Common\Node;
use Pipeline\Common\SourceInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

/**
 * Xml Pipeline Flattener.
 *
 * @phpstan-import-type FetchedData from SourceInterface
 * @phpstan-import-type FlattenerConfig from FlattenerInterface
 * @phpstan-import-type Item from Node
 */
class XmlFlattener extends Node implements FlattenerInterface
{

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Shared instance of the Logger class
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Find and return the element holding the item list.
     *
     * @param SimpleXMLElement $data Full data
     *
     * @return SimpleXMLElement|null Root element or NULL if none could be found
     */
    protected function findRootElement(SimpleXMLElement $data): ?SimpleXMLElement
    {
        // Currently we assume the first element we find that
        // has more then 1 child is the element we're searching for.
        foreach ($data->children() as $value)
        {
            if ($value->count() > 1)
            {
                return $value;
            }

            $root = $this->findRootElement($value);

            if ($root !== NULL)
            {
                return $root;
            }
        }

        return NULL;
    }

    /**
     * Find and return the root.
     *
     * @param SimpleXMLElement $source Full data
     * @param string|null      $root   Root location.
     *
     * @return SimpleXMLElement|null Root element or NULL if none could be found
     */
    protected function getRoot(SimpleXMLElement $source, $root = NULL): ?SimpleXMLElement
    {
        if (!isset($root))
        {
            return $this->findRootElement($source);
        }

        $result = $source->xpath($root);

        if (empty($result))
        {
            return NULL;
        }

        return $result[0];
    }

    /**
     * Resolve xpath and return result.
     *
     * @param SimpleXMLElement $source   Source data
     * @param string           $xpath    Xpath location.
     * @param bool             $simplify Whether to simplify the result or not
     *
     * @return array<mixed>|string|false|null Element, FALSE on error, NULL if none could be found
     */
    private function resolveXpath(SimpleXMLElement $source, string $xpath, bool $simplify = TRUE): array|string|false|null
    {
        $results = $source->xpath($xpath);

        if ($results === FALSE)
        {
            return FALSE;
        }

        if (empty($results))
        {
            return NULL;
        }

        if ($simplify === FALSE)
        {
            return $results;
        }

        // Cast SimpleXMLElements to more useful simple types.
        // This avoids single values otherwise being wrapped in two arrays
        $results = array_map(function ($a) { return count($a) <= 1 ? (string) $a : (array) $a; }, $results);

        // In case of a single result from the XPath query, we'd have to
        // run a select processor on the item to get to the value
        return count($results) == 1 ? $results[0] : $results;
    }

    /**
     * Checks if a path is a root referencing path.
     *
     * @param string $xpath Path string to check.
     *
     * @return bool
     */
    private function isRootReference(string $xpath): bool
    {
        return (isset($xpath[0]) && $xpath[0] === '/' && $xpath[1] !== '/');
    }

    /**
     * Flatten source data into a single array of items.
     *
     * @param FetchedData     $data   Source data to flatten
     * @param FlattenerConfig $config Configuration parameters necessary to process the data
     *
     * @return Item[] Array of flattened data
     */
    public function process(array $data, array $config): array
    {
        libxml_use_internal_errors(TRUE);

        $rootpath  = NULL;
        $namespace = NULL;

        if (array_key_exists('config', $config) && array_key_exists('root', $config['config']))
        {
            $rootpath = $config['config']['root'];
        }

        if (array_key_exists('config', $config) && array_key_exists('namespace', $config['config']))
        {
            $namespace = $config['config']['namespace'];
        }

        if (array_key_exists('fields', $config) == FALSE)
        {
            return [];
        }

        $output = [];

        foreach ($data as $source)
        {
            if (!is_string($source))
            {
                continue;
            }

            try
            {
                $source = new SimpleXMLElement($source);
            }
            catch (Exception $e)
            {
                continue;
            }

            $namespaces   = $source->getNamespaces();
            $namespaceUrl = $namespaces[$namespace] ?? NULL;

            $root = $this->getRoot($source, $rootpath);

            if ($root === NULL)
            {
                continue;
            }

            foreach ($root->children($namespaceUrl) as $object)
            {
                if ($object->count() === 0)
                {
                    continue;
                }

                $result = [];

                foreach ($config['fields'] as $destination => $origin)
                {
                    if (is_string($origin))
                    {
                        $child = $object->{$origin};

                        if ($child instanceof SimpleXMLElement)
                        {
                            $result[$destination] = (string) $child;
                        }
                    }
                    elseif (is_array($origin) && array_key_exists('path', $origin) && is_string($origin['path']))
                    {
                        $element = $this->isRootReference($origin['path']) ? $source : $object;

                        $simplify = !array_key_exists('simplify', $origin) || $origin['simplify'] === TRUE;

                        $result[$destination] = $this->resolveXpath($element, $origin['path'], $simplify);
                    }

                    if (array_key_exists($destination, $result))
                    {
                        continue;
                    }

                    $result[$destination] = NULL;
                }

                $output[] = $result;
            }
        }

        return $output;
    }

}

?>
