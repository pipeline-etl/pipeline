<?php

/**
 * This file contains the Pipeline class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline;

use Lunr\Ticks\Profiling\Profiler;
use Pipeline\Common\Exceptions\InvalidConfigurationException;
use Pipeline\Common\FlattenerInterface;
use Pipeline\Common\InfoInterface;
use Pipeline\Common\Locator;
use Pipeline\Common\Node;
use Pipeline\Common\ProcessorInterface;
use Pipeline\Common\ProcessorRunnerInterface;
use Pipeline\Common\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Pipeline class.
 *
 * @phpstan-import-type FetchedData from SourceInterface
 * @phpstan-import-type FlattenerConfig from FlattenerInterface
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessedItem from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 * @phpstan-import-type ProcessorIdentifier from ProcessorRunnerInterface
 * @phpstan-import-type SourceConfig from SourceInterface
 */
class Pipeline implements ProcessorRunnerInterface
{

    /**
     * Shared instance of the Locator class.
     * @var Locator
     */
    protected readonly Locator $locator;

    /**
     * Shared instance of the Logger class.
     * @var LoggerInterface
     */
    protected readonly LoggerInterface $logger;

    /**
     * Pipeline config file
     * @var string
     */
    protected string $file;

    /**
     * Pipeline name
     * @var string
     */
    protected string $name;

    /**
     * List of sources
     * @var list<array<string, SourceConfig>>
     */
    protected array $sources;

    /**
     * Flattener
     * @var array<string, FlattenerConfig>
     */
    protected array $flattener;

    /**
     * Parser
     * @var array<string, ProcessorConfig>
     */
    protected array $parser;

    /**
     * Object keeping track of the pipeline run.
     * @var Profiler
     */
    protected readonly Profiler $profiler;

    /**
     * Constructor.
     *
     * @param Locator $locator Locator to load classes
     * @param string  $file    Path to pipeline config file
     */
    public function __construct(Locator $locator, string $file)
    {
        $this->locator = $locator;
        $this->logger  = $locator->getLogger();
        $this->file    = $file;
        $this->name    = basename($file, '.json');

        $this->sources   = [];
        $this->flattener = [];
        $this->parser    = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->file = '';
        $this->name = '';

        $this->sources   = [];
        $this->flattener = [];
        $this->parser    = [];
    }

    /**
     * Load the pipeline JSON file and setup the components.
     *
     * @param InfoInterface|null $info Instance of an Info Object
     *
     * @throws InvalidConfigurationException Pipeline fails to load.
     *
     * @return true Returns TRUE on success, throws exception on failure
     */
    public function load(?InfoInterface $info): bool
    {
        if ($info !== NULL)
        {
            $info->setPipelineIdentifier($this->name);
            $this->profiler = $info->getProfiler();
        }

        $this->reportStep('Load JSON definition');

        $content = file_get_contents($this->file);

        if ($content === FALSE)
        {
            throw new InvalidConfigurationException('Failed reading pipeline description file!');
        }

        $pipeline = json_decode($content, TRUE);

        if (json_last_error() !== JSON_ERROR_NONE)
        {
            throw new InvalidConfigurationException('Failed parsing pipeline description!');
        }

        if (!is_array($pipeline))
        {
            throw new InvalidConfigurationException('Pipeline description has invalid format!');
        }

        foreach ([ 'sources', 'flattener' ] as $component)
        {
            if (!array_key_exists($component, $pipeline))
            {
                throw new InvalidConfigurationException("Failed loading pipeline description! Missing pipeline $component");
            }
        }

        if ($info !== NULL)
        {
            $infoData = $pipeline[$info->getInfoIdentifier()] ?? [];

            if (is_array($infoData))
            {
                /** @var array<string, mixed> $infoData */
                $info->populate($infoData);
            }
        }

        /** @var list<array<string, SourceConfig>> $sources */
        $sources = $pipeline['sources'];

        /** @var array<string, FlattenerConfig> $flattener */
        $flattener = $pipeline['flattener'];

        $this->sources   = $sources;
        $this->flattener = $flattener;

        if (array_key_exists('preprocessors', $pipeline) || array_key_exists('processors', $pipeline))
        {
            /** @var ProcessorConfig $parserConfig */
            $parserConfig = [
                'preprocessors' => $pipeline['preprocessors'] ?? [],
                'processors'    => $pipeline['processors'] ?? [],
            ];

            $this->parser = [
                'default' => $parserConfig,
            ];
        }
        elseif (array_key_exists('parser', $pipeline))
        {
            /** @var array<string, ProcessorConfig> $parser */
            $parser = $pipeline['parser'];

            $this->parser = $parser;
        }

        return TRUE;
    }

    /**
     * Set a profiler.
     *
     * The profiler is normally set in load() to the profiler in the Info object.
     * This provides an alternative means to set it when no Info object is passed
     * to load.
     *
     * @param Profiler $profiler Object keeping track of the pipeline run.
     *
     * @return void
     */
    public function setProfiler(Profiler $profiler): void
    {
        if (isset($this->profiler))
        {
            return;
        }

        $this->profiler = $profiler;

        $this->profiler->addTag('pipeline', $this->name);
    }

    /**
     * Process the pipeline.
     *
     * @param string|null $environment Environment config to use for fetching sources
     * @param bool        $record      Whether to record the fetched sources or not
     * @param string|null $mock        Path to previously recorded source data
     *
     * @return ProcessedItem[] Processed data
     */
    public function process(?string $environment, bool $record = FALSE, ?string $mock = NULL): array
    {
        $this->reportStep('Process sources');

        $data = $this->processSources($environment, $record, $mock);

        $this->reportStep('Flattening data');

        $data = $this->processFlattener($data);

        $this->reportStep('Configuring Parser');

        $data = $this->processParser($data);

        return $data;
    }

    /**
     * Process sources.
     *
     * @param string|null $environment Environment config to use for fetching sources
     * @param bool        $record      Whether to record the fetched sources or not
     * @param string|null $mock        Path to previously recorded source data
     *
     * @return FetchedData Processed source data
     */
    protected function processSources(?string $environment, bool $record, ?string $mock): array
    {
        if ($mock !== NULL)
        {
            $this->logger->info('Using recorded source data from ' . $mock);

            $data = unserialize(trim(file_get_contents($mock) ?: ''));

            // @phpstan-ignore return.type (deep validation of test data not worth the effort)
            return is_array($data) ? $data : [];
        }

        $data = [];

        foreach ($this->sources as &$source)
        {
            $name = array_key_first($source);

            if ($name === NULL)
            {
                continue;
            }

            /** @var SourceConfig $config */
            $config = $source[$name];

            $class = $this->locator->getSource($name);

            if (!is_object($class))
            {
                continue;
            }

            // Definitely no environments
            if ($config === [])
            {
                $data = array_merge($data, $class->fetch($config));
                continue;
            }

            // Extract defined environments. Empty array means no environments were found.
            $environments = [];

            foreach ($config as $key => $value)
            {
                if (!is_array($value))
                {
                    $environments = [];
                    break;
                }

                $environments[] = $key;
            }

            if ($environment !== NULL && in_array($environment, $environments))
            {
                /** @var SourceConfig $config */
                $config = $config[$environment];
            }
            elseif (in_array('production', $environments))
            {
                if ($environment !== NULL)
                {
                    $this->logger->warning('Environment "' . $environment . '" is unknown! Proceeding with "production"');
                }

                /** @var SourceConfig $config */
                $config = $config['production'];
            }
            elseif ($environments !== [])
            {
                if ($environment === NULL)
                {
                    $this->logger->warning('Production environment not set and no environment option specified! Skipping!');
                }
                else
                {
                    $this->logger->warning('Environment "' . $environment . '" is unknown! No valid environment found! Skipping!');
                }

                continue;
            }

            $data = array_merge($data, $class->fetch($config));
        }

        unset($source);

        if ($record === TRUE)
        {
            $file = sys_get_temp_dir() . '/' . $this->name . '.data';
            file_put_contents($file, serialize($data));
            $this->logger->info('Recorded source data at ' . $file);
        }

        return $data;
    }

    /**
     * Process flattener.
     *
     * @param FetchedData $data Data to process
     *
     * @return Item[] Flattened data
     */
    protected function processFlattener(array $data): array
    {
        $name   = array_key_first($this->flattener);
        $config = array_shift($this->flattener);

        if ($name === NULL || $config === NULL)
        {
            throw new InvalidConfigurationException('No flattener configured!');
        }

        $class = $this->locator->getFlattener($name);

        if (!is_object($class))
        {
            throw new InvalidConfigurationException('Flattener "' . $name . '" not found!');
        }

        $data = $class->process($data, $config);

        return $data;
    }

    /**
     * Process parser.
     *
     * @param Item[] $data Data to process
     *
     * @return ProcessedItem[] Parsed data
     */
    protected function processParser(array $data): array
    {
        $name   = array_key_first($this->parser);
        $config = array_shift($this->parser);

        if ($name === NULL || $config === NULL)
        {
            return $this->stripNonScalarValues($data);
        }

        $class = $this->locator->getParser($name);

        if (!is_object($class))
        {
            throw new InvalidConfigurationException('Parser "' . $name . '" not found!');
        }

        $data = $class->process($data, $config);

        return $data;
    }

    /**
     * Strip non-scalar values from items, replacing them with NULL.
     *
     * @param Item[] $data Data to strip
     *
     * @return ProcessedItem[] Stripped data
     */
    private function stripNonScalarValues(array $data): array
    {
        $hasNonScalar = FALSE;

        $result = array_map(
            function (array $item) use (&$hasNonScalar): array
            {
                return array_map(
                    function (mixed $value) use (&$hasNonScalar): bool|float|int|string|null
                    {
                        if (is_scalar($value) || $value === NULL)
                        {
                            return $value;
                        }

                        $hasNonScalar = TRUE;

                        return NULL;
                    },
                    $item
                );
            },
            $data
        );

        if ($hasNonScalar)
        {
            $this->logger->warning('Stripped non-scalar values from pipeline output without parser');
        }

        return $result;
    }

    /**
     * Run a processor.
     *
     * @param ProcessorIdentifier $processor       Processor identifier
     * @param ProcessorConfig     $processorConfig Processor configuration
     * @param Item                $item            Data to process
     *
     * @return Item Processed item
     */
    public function run(string $processor, array $processorConfig, array $item): array
    {
        $class = $this->locator->getProcessor($processor);

        if (!is_object($class))
        {
            return $item;
        }

        return $class->process($item, $processorConfig);
    }

    /**
     * Report a pipeline step
     *
     * @param string $message Message to log
     *
     * @return void
     */
    private function reportStep(string $message): void
    {
        $this->profiler->startNewSpan($message);
        $this->logger->notice($message);
    }

}

?>
