<?php

/**
 * This file contains the ProcessorPreprocessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Preprocessors;

use Pipeline\Common\FlowControlProcessor;
use Pipeline\Common\Node;
use Pipeline\Common\PreprocessorInterface;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Processor Pipeline Preprocessor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class ProcessorPreprocessor extends FlowControlProcessor implements PreprocessorInterface
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
            $this->logIncompleteConfiguration('No processors defined to run!');
            return $data;
        }

        if (!array_is_list($config))
        {
            $this->logInvalidConfiguration('Processors not defined in a list!');
            return $data;
        }

        foreach ($config as $index => $processorConfig)
        {
            [ $name, $stepConfig ] = [ key($processorConfig), current($processorConfig) ];

            if (!is_string($name))
            {
                $this->logInvalidConfiguration('Processor identifier is not a string!', $index);
                continue;
            }

            if (!is_array($stepConfig))
            {
                $this->logInvalidConfiguration("Configuration for processor '$name' is not an array!", $index);
                continue;
            }

            foreach ($data as &$item)
            {
                /** @var ProcessorConfig $stepConfig */
                $item = $this->runner->run($name, $stepConfig, $item);
            }

            unset($item);
        }

        return $data;
    }

}

?>
