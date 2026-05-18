<?php

/**
 * This file contains the CrossReferencePreprocessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Preprocessors;

use Pipeline\Common\Node;
use Pipeline\Common\PreprocessorInterface;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Cross Reference Pipeline Preprocessor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class CrossReferencePreprocessor extends Node implements PreprocessorInterface
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
     * Process source data.
     *
     * @param Item[]          $data   Source data to process
     * @param ProcessorConfig $config Configuration parameters necessary to process the data
     *
     * @return Item[] Processed data
     */
    public function process(array $data, array $config): array
    {
        if (empty($config))
        {
            $this->logIncompleteConfiguration('No configuration defined!');
            return $data;
        }

        if (!isset($config['identifier']))
        {
            $this->logIncompleteConfiguration("'identifier' not defined!");
            return $data;
        }

        /** @phpstan-ignore booleanNot.alwaysFalse (https://github.com/phpstan/phpstan/issues/12533) */
        if (!is_array($config['identifier']))
        {
            $this->logInvalidConfiguration("'identifier' is not an array!");
            return $data;
        }

        if (!isset($config['field']))
        {
            $this->logIncompleteConfiguration("'field' not defined!");
            return $data;
        }

        /** @phpstan-ignore booleanNot.alwaysFalse (https://github.com/phpstan/phpstan/issues/12533) */
        if (!is_array($config['field']))
        {
            $this->logInvalidConfiguration("'field' is not an array!");
            return $data;
        }

        /** @var array<string, string> $identifier */
        $identifier = $config['identifier'];

        /** @var array<string, string> $fields */
        $fields = $config['field'];

        foreach ($data as &$item)
        {
            /*
             * Some items will match themselves already. For those we don't
             * want to pay the full lookup-costs for going through the entire
             * array of items once again, so we check first if we have one of
             * those.
             */
            $match = $this->crossReference($item, $item, $identifier, $fields);

            if ($match === TRUE)
            {
                continue;
            }

            /*
             * Look through the entire array of items and try to find one that
             * matches our search criteria.
             */
            foreach ($data as $other)
            {
                $match = $this->crossReference($item, $other, $identifier, $fields);

                if ($match === TRUE)
                {
                    break;
                }
            }

            /*
             * If we didn't find a match, we still create the keys to keep all
             * items uniform, but set the value to NULL.
             */
            if ($match === TRUE)
            {
                continue;
            }

            foreach ($fields as $destination => $origin)
            {
                // Don't overwrite existing values
                if (array_key_exists($destination, $item))
                {
                    continue;
                }

                $item[$destination] = NULL;
            }
        }

        unset($item);

        /** @var Item[] $data */
        return $data;
    }

    /**
     * Find a matching item and apply the cross-reference.
     *
     * @param Item                  $item       Base item
     * @param Item                  $other      Comparison item
     * @param array<string, string> $identifier Set of search criteria
     * @param array<string, string> $fields     Set of destination/origin fields for cross-referencing
     *
     * @return bool TRUE if a cross-reference was applied, FALSE otherwise
     */
    protected function crossReference(array &$item, array $other, array $identifier, array $fields): bool
    {
        foreach ($identifier as $otherKey => $itemKey)
        {
            if ($other[$otherKey] !== $item[$itemKey])
            {
                return FALSE;
            }
        }

        foreach ($fields as $destination => $origin)
        {
            $item[$destination] = $other[$origin];
        }

        return TRUE;
    }

}

?>
