<?php

/**
 * This file contains the ForeachProcessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Processors;

use Pipeline\Common\FlowControlProcessor;
use Pipeline\Common\Node;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Foreach Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class ForeachProcessor extends FlowControlProcessor implements ProcessorInterface
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
        if (!array_key_exists('field', $config))
        {
            $this->logIncompleteConfiguration("'field' is missing");

            return $item;
        }

        /** @phpstan-ignore function.impossibleType (https://github.com/phpstan/phpstan/issues/12533) */
        if (!is_string($config['field']))
        {
            $this->logInvalidConfiguration("'field' is not a string");

            return $item;
        }

        /** @phpstan-ignore deadCode.unreachable (https://github.com/phpstan/phpstan/issues/12533) */
        if (!array_key_exists('processor', $config) && !array_key_exists('processors', $config))
        {
            $this->logIncompleteConfiguration('No processor defined');

            return $item;
        }

        $field = $config['field'];

        if (!array_key_exists($field, $item))
        {
            $this->log(LogLevel::WARNING, "Field '$field' does not exist");

            return $item;
        }

        $list = [];

        if ($item[$field] === NULL)
        {
            return $item;
        }

        if (!is_array($item[$field]))
        {
            $this->log(LogLevel::WARNING, "Field '$field' is not an array. Setting value to NULL");

            $item[$field] = NULL;
            return $item;
        }

        foreach ($item[$field] as $subItem)
        {
            if (array_key_exists('processor', $config))
            {
                [ $name, $stepConfig ] = [ key($config['processor']), current($config['processor']) ];
                $subItem               = $this->runner->run($name, $stepConfig, $subItem);
            }
            else
            {
                foreach ($config['processors'] as $processor)
                {
                    [ $name, $stepConfig ] = [ key($processor), current($processor) ];
                    $subItem               = $this->runner->run($name, $stepConfig, $subItem);
                }
            }

            $list[] = $subItem;
        }

        $item[$field] = $list;

        return $item;
    }

}

?>
