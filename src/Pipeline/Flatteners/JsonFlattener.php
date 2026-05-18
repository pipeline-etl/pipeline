<?php

/**
 * This file contains the JsonFlattener class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Flatteners;

use Pipeline\Common\FlattenerInterface;
use Pipeline\Common\Node;
use Pipeline\Common\SourceInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Json Pipeline Flattener.
 *
 * @phpstan-import-type FetchedData from SourceInterface
 * @phpstan-import-type FlattenerConfig from FlattenerInterface
 * @phpstan-import-type Item from Node
 */
class JsonFlattener extends Node implements FlattenerInterface
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
     * @param mixed       $data Full data
     * @param string|null $root Root location.
     *
     * @return array<array-key, mixed>|null Root element or NULL if none could be found
     */
    protected function getRootElement(mixed $data, ?string $root = NULL): ?array
    {
        if (!is_null($root))
        {
            $result = $this->resolveJsonPath($data, $root);

            return is_array($result) ? $result : NULL;
        }

        if (is_array($data))
        {
            return $data;
        }

        if (!is_object($data))
        {
            return NULL;
        }

        foreach (get_object_vars($data) as $value)
        {
            $found = $this->getRootElement($value);

            if ($found !== NULL)
            {
                return $found;
            }
        }

        return NULL;
    }

    /**
     * Convert a JSONPath root location to a JSON Pointer.
     *
     * @param string $root Root location as JSONPath
     *
     * @return string JSON Pointer to root element
     */
    protected function getRootPointer(string $root): string
    {
        $pointer = str_replace([ '.', '[', '$', ']' ], '/', $root);
        $pointer = str_replace('//', '/', $pointer);
        $pointer = rtrim($pointer, '/');

        if (empty($pointer) || $pointer[0] != '/')
        {
            $pointer = '/' . $pointer;
        }

        return $pointer;
    }

    /**
     * Detect the JSON Pointer to the root array element in a data structure.
     *
     * @param mixed $data Decoded JSON data
     *
     * @return string JSON Pointer to the detected root array
     */
    private function detectRootPointer(mixed $data): string
    {
        if (is_array($data))
        {
            return '/';
        }

        if (!is_object($data))
        {
            return '/';
        }

        foreach (get_object_vars($data) as $key => $value)
        {
            if (is_array($value))
            {
                return '/' . $key;
            }

            if (!is_object($value))
            {
                continue;
            }

            $subpointer = $this->detectRootPointer($value);

            if ($subpointer !== '/')
            {
                return '/' . $key . $subpointer;
            }
        }

        return '/';
    }

    /**
     * Resolve a given JSONPath against an object.
     *
     * @param mixed  $object Item data
     * @param string $path   JSONPath
     *
     * @return mixed Value of the key the JSONPath resolved to, NULL if path resolution failed
     */
    protected function resolveJsonPath(mixed $object, string $path): mixed
    {
        $properties = explode('.', $path);

        $subject = $object;

        foreach ($properties as $property)
        {
            if ($property == '$')
            {
                continue;
            }

            $start = strpos($property, '[');

            if ($start === FALSE)
            {
                $name  = $property;
                $index = NULL;
            }
            else
            {
                $name  = substr($property, 0, $start);
                $index = (int) substr($property, $start + 1, strpos($property, ']') - $start - 1);
            }

            if (!is_object($subject) || !property_exists($subject, $name))
            {
                return NULL;
            }

            if ($index === NULL)
            {
                $subject = $subject->$name;
            }
            else
            {
                if (!is_array($subject->$name) || !array_key_exists($index, $subject->$name))
                {
                    return NULL;
                }

                $subject = $subject->{$name}[$index];
            }
        }

        return $subject;
    }

    /**
     * Resolve a given JSON Pointer against an object.
     *
     * @param mixed  $object  Item data
     * @param string $pointer JSON Pointer
     *
     * @return mixed Value of the key the JSON Pointer resolved to, NULL if pointer resolution failed
     */
    protected function resolveJsonPointer(mixed $object, string $pointer): mixed
    {
        $properties = explode('/', $pointer);

        if (substr($pointer, -1) == '#')
        {
            $value = array_pop($properties);

            return rtrim($value, '#');
        }

        $subject = $object;

        foreach ($properties as $property)
        {
            if ($property == '')
            {
                continue;
            }

            if (!is_object($subject) && !is_array($subject))
            {
                return NULL;
            }

            if (is_object($subject) && !property_exists($subject, $property))
            {
                return NULL;
            }

            if (is_array($subject) && !array_key_exists($property, $subject))
            {
                return NULL;
            }

            if (is_object($subject))
            {
                $subject = $subject->$property;
            }
            else
            {
                $subject = $subject[$property];
            }
        }

        return $subject;
    }

    /**
     * Combine the JSON Pointer to a base element, and a relative JSON Pointer from that element.
     *
     * @param string $pointer Relative JSON Pointer
     * @param string $base    Absolute JSON Pointer to the base element
     *
     * @return string Combined, absolute JSON Pointer
     */
    protected function getAbsoluteJsonPointer(string $pointer, string $base): string
    {
        $root     = explode('/', $base);
        $relative = explode('/', $pointer);

        for ($i = (int) $relative[0]; $i > 0; $i--)
        {
            array_pop($root);
        }

        array_shift($relative);

        $absolute = implode('/', array_merge($root, $relative));

        if (substr($pointer, -1) == '#' && substr($absolute, -1) != '#')
        {
            $absolute .= '#';
        }

        return $absolute;
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
        $rootpath = NULL;

        if (array_key_exists('config', $config) && array_key_exists('root', $config['config']))
        {
            $rootpath = $config['config']['root'];
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

            // Remove UTF-8 BOM
            if (substr($source, 0, 3) === chr(239) . chr(187) . chr(191))
            {
                $source = substr($source, 3);
            }

            $source = json_decode($source);

            if (json_last_error() !== JSON_ERROR_NONE)
            {
                continue;
            }

            $root = $this->getRootElement($source, $rootpath);

            if ($root === NULL)
            {
                continue;
            }

            if ($rootpath !== NULL)
            {
                $rootPointer = $this->getRootPointer($rootpath);
            }
            else
            {
                $rootPointer = $this->detectRootPointer($source);
            }

            foreach ($root as $key => $object)
            {
                if (!($object instanceof stdClass))
                {
                    continue;
                }

                $result = [];

                foreach ($config['fields'] as $destination => $origin)
                {
                    if (is_string($origin) && property_exists($object, $origin))
                    {
                        $result[$destination] = $object->$origin;
                    }

                    if (is_array($origin))
                    {
                        if (array_key_exists('path', $origin))
                        {
                            $element = $origin['path'][0] == '$' ? $source : $object;

                            $result[$destination] = $this->resolveJsonPath($element, $origin['path']);
                        }
                        elseif (array_key_exists('pointer', $origin))
                        {
                            if ($origin['pointer'][0] == '/')
                            {
                                $pointer = $origin['pointer'];
                            }
                            else
                            {
                                $pointer = $this->getAbsoluteJsonPointer($origin['pointer'], $rootPointer . '/' . $key);
                            }

                            $result[$destination] = $this->resolveJsonPointer($source, $pointer);
                        }
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

        /** @var Item[] $output */
        return $output;
    }

}

?>
