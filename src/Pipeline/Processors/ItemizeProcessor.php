<?php

/**
 * This file contains the ItemizeProcessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Processors;

use Pipeline\Common\Node;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Itemize Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class ItemizeProcessor extends Node implements ProcessorInterface
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
     * @param Item            $item   Source data to process
     * @param ProcessorConfig $config Configuration parameters necessary to process the data
     *
     * @return Item Processed data
     */
    public function process(array $item, array $config): array
    {
        if (!array_key_exists('fields', $config))
        {
            $this->logIncompleteConfiguration("'fields' is missing");
            return $item;
        }

        /** @phpstan-ignore function.alreadyNarrowedType (https://github.com/phpstan/phpstan/issues/12533) */
        if (!is_array($config['fields']))
        {
            $this->logInvalidConfiguration("'fields' is not an array");
            return $item;
        }

        foreach ($config['fields'] as $field)
        {
            if (!is_string($field))
            {
                continue;
            }

            if (!array_key_exists($field, $item))
            {
                $this->log(LogLevel::WARNING, "Skip '{$field}', field does not exist");
                continue;
            }

            if (!is_array($item[$field]))
            {
                $this->log(LogLevel::WARNING, "Skip '{$field}', field is not an array");
                continue;
            }

            if ($item[$field] !== [] && array_keys($item[$field]) !== range(0, count($item[$field]) - 1))
            {
                $this->log(LogLevel::WARNING, "Skip '{$field}', field is not an indexed array");
                continue;
            }

            $keys = [];

            foreach ($item[$field] as &$subItem)
            {
                if (is_object($subItem))
                {
                    $subItem = (array) $subItem;
                }

                if (!is_array($subItem))
                {
                    continue;
                }

                $keys = array_unique(array_merge($keys, array_keys($subItem)));
            }

            foreach ($item[$field] as &$subItem)
            {
                if (!is_array($subItem))
                {
                    continue;
                }

                $diffKeys = array_diff($keys, array_keys($subItem));

                foreach ($diffKeys as $key)
                {
                    $subItem[$key] = NULL;
                }

                ksort($subItem);
            }
        }

        return $item;
    }

}

?>
