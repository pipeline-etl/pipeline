<?php

/**
 * This file contains the ContainerSource class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Sources;

use Pipeline\Common\Node;
use Pipeline\Common\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Container Pipeline Source.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type SourceConfig from SourceInterface
 * @phpstan-import-type FetchedData from SourceInterface
 */
class ContainerSource extends Node implements SourceInterface
{

    /**
     * Data to retain for pipeline.
     * @var FetchedData
     */
    protected array $data;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Shared instance of the Logger class
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->data = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Set the data.
     *
     * @param FetchedData $data Data to set for the source
     *
     * @return void
     */
    public function set(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Add data to source.
     *
     * @param string|Item[] $data Data to add to the source
     *
     * @return void
     */
    public function add(string|array $data): void
    {
        $this->data[] = $data;
    }

    /**
     * Retrieve source data to process in the pipeline.
     *
     * @param SourceConfig $config Configuration parameters necessary to retrieve the data
     *
     * @return FetchedData Array of results fetched from the source
     */
    public function fetch(array $config): array
    {
        return $this->data;
    }

}

?>
