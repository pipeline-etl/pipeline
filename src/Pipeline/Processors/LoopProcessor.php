<?php

/**
 * This file contains the LoopProcessor class.
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
 * Loop Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class LoopProcessor extends FlowControlProcessor implements ProcessorInterface
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
        if (!array_key_exists($config['field'], $item))
        {
            $this->log(LogLevel::WARNING, "Field '{$config['field']}' does not exist");

            return $item;
        }

        if (!is_array($item[$config['field']]))
        {
            $this->log(LogLevel::WARNING, "Field '{$config['field']}' is not an array");

            return $item;
        }

        if (!array_key_exists('processors', $config))
        {
            $this->logIncompleteConfiguration('No processors defined');

            return $item;
        }

        foreach (array_values($item[$config['field']]) as $index => $value)
        {
            foreach ($config['processors'] as $processor)
            {
                if (!is_array($processor))
                {
                    continue;
                }

                if (!array_key_exists('select', $processor))
                {
                    [ $name, $stepConfig ] = [ key($processor), current($processor) ];
                    $item                  = $this->runner->run($name, $stepConfig, $item);

                    continue;
                }

                if (is_array($processor['select']))
                {
                    foreach ($processor['select'] as &$select)
                    {
                        if (!is_array($select)
                            || !array_key_exists('options', $select) || !is_array($select['options'])
                            || !array_key_exists('key', $select['options'])
                            || $select['options']['key'] !== 'LOOP-INDEX'
                        )
                        {
                            continue;
                        }

                        $select['options']['key'] = $index;
                    }
                }

                [ $name, $stepConfig ] = [ key($processor), current($processor) ];
                $item                  = $this->runner->run($name, $stepConfig, $item);
            }
        }

        return $item;
    }

}

?>
