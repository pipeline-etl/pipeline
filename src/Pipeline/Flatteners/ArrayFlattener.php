<?php

/**
 * This file contains the ArrayFlattener class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Flatteners;

use Pipeline\Common\FlattenerInterface;
use Pipeline\Common\Node;
use Pipeline\Common\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Array Pipeline Flattener.
 *
 * @phpstan-import-type FlattenerConfig from FlattenerInterface
 * @phpstan-import-type FetchedData from SourceInterface
 * @phpstan-import-type Item from Node
 */
class ArrayFlattener extends Node implements FlattenerInterface
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
     * Flatten source data into a single array of items.
     *
     * @param FetchedData     $data   Source data to flatten
     * @param FlattenerConfig $config Configuration parameters necessary to process the data
     *
     * @return Item[] Array of flattened data
     */
    public function process(array $data, array $config): array
    {
        if (!is_array($data[0]))
        {
            // First item in the list is not an array. It's safe to assume the rest isn't either
            return [];
        }

        /**
         * @var list<Item[]> $data
         */
        return array_merge(...$data);
    }

}

?>
