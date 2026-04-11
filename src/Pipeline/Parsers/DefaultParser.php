<?php

/**
 * This file contains the DefaultParser class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Parsers;

use Lunr\Ticks\Profiling\Profiler;
use Pipeline\Common\Locator;
use Pipeline\Common\Node;
use Pipeline\Common\Parser;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Default Pipeline Parser.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class DefaultParser extends Parser
{

    /**
     * Shared instance of the Locator class.
     * @var Locator
     */
    protected readonly Locator $locator;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger   Shared instance of the Logger class
     * @param Profiler        $profiler Instance of the Profiler class
     * @param Locator         $locator  Shared instance of the Locator class
     */
    public function __construct(LoggerInterface $logger, Profiler $profiler, Locator $locator)
    {
        parent::__construct($logger, $profiler);

        $this->locator = $locator;
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
        if (!empty($config) && array_is_list($config))
        {
            $this->logInvalidConfiguration('Parser configuration is not an associative array!');
            return $data;
        }

        // https://github.com/phpstan/phpstan/issues/12533
        /** @var array<int, ProcessorConfig> $preprocessors */
        $preprocessors = $config['preprocessors'] ?? []; // @phpstan-ignore varTag.nativeType (ProcessorConfig array union incorrectly collapsed)

        // https://github.com/phpstan/phpstan/issues/12533
        /** @var array<int, ProcessorConfig> $processors */
        $processors = $config['processors'] ?? []; // @phpstan-ignore varTag.nativeType (ProcessorConfig array union incorrectly collapsed)

        $this->reportStep('Run Preprocessors');
        $data = $this->runPreprocessors($data, $preprocessors);

        $this->reportStep('Run Processors');
        $data = $this->runProcessors($data, $processors);

        return $data;
    }

    /**
     * Run preprocessors on the dataset.
     *
     * @param Item[]                      $data   Source data to process
     * @param array<int, ProcessorConfig> $config Preprocessor configuration
     *
     * @return Item[] Processed data
     */
    protected function runPreprocessors(array $data, array $config): array
    {
        if (empty($config))
        {
            return $data;
        }

        if (!array_is_list($config))
        {
            $this->logInvalidConfiguration('Preprocessors not defined in a list!');
            return $data;
        }

        foreach ($config as $index => $preprocessorConfig)
        {
            [ $name, $stepConfig ] = [ key($preprocessorConfig), current($preprocessorConfig) ];

            if (!is_string($name))
            {
                $this->logInvalidConfiguration('Preprocessor identifier is not a string!', $index);
                continue;
            }

            if (!is_array($stepConfig))
            {
                $this->logInvalidConfiguration("Configuration for preprocessor '$name' is not an array!", $index);
                continue;
            }

            $class = $this->locator->getPreprocessor($name);

            if (!is_object($class))
            {
                continue;
            }

            /** @var ProcessorConfig $stepConfig */
            $data = $class->process($data, $stepConfig);
        }

        return $data;
    }

    /**
     * Run processors on each item in the dataset.
     *
     * @param Item[]                      $data   Source data to process
     * @param array<int, ProcessorConfig> $config Processor configuration
     *
     * @return Item[] Processed data
     */
    protected function runProcessors(array $data, array $config): array
    {
        if (empty($config))
        {
            return $data;
        }

        if (!array_is_list($config))
        {
            $this->logInvalidConfiguration('Processors not defined in a list!');
            return $data;
        }

        $steps = [];

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

            $class = $this->locator->getProcessor($name);

            if (!is_object($class))
            {
                continue;
            }

            $steps[] = [ $class, $stepConfig ];
        }

        foreach ($data as &$item)
        {
            foreach ($steps as [ $class, $stepConfig ])
            {
                /** @var ProcessorConfig $stepConfig */
                $item = $class->process($item, $stepConfig);
            }
        }

        unset($item);

        return $data;
    }

}

?>
