<?php

/**
 * This file contains the ValueProcessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Processors;

use Pipeline\Common\Node;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Value Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class ValueProcessor extends Node implements ProcessorInterface
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
        if (empty($config))
        {
            $this->logIncompleteConfiguration('No values defined to set!');
            return $item;
        }

        foreach ($config as $field => $value)
        {
            // We use array_key_exists() instead of isset() since we don't want to override NULL values
            if (array_key_exists($field, $item))
            {
                continue;
            }

            $item[(string) $field] = $value;
        }

        return $item;
    }

}

?>
