<?php

/**
 * This file contains the SelectProcessor class.
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
 * Select Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class SelectProcessor extends Node implements ProcessorInterface
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
        foreach ($config as $index => $step)
        {
            /** @phpstan-ignore function.alreadyNarrowedType (https://github.com/phpstan/phpstan/issues/12533) */
            if (!is_array($step))
            {
                continue;
            }

            if (!array_key_exists('field', $step))
            {
                $this->logIncompleteConfiguration("'field' is missing", $index);
                return $item;
            }

            $field = $step['field'];

            if (!is_string($field))
            {
                $this->logInvalidConfiguration("'field' is not a string", $index);
                return $item;
            }

            $return   = NULL;
            $selector = $step['selector'] ?? 'default';

            if (!array_key_exists($field, $item))
            {
                $this->log(LogLevel::WARNING, "Field '$field' does not exist in the item", $index);
                $item[$field] = NULL;
                return $item;
            }

            switch ($selector)
            {
                case 'first':
                    if (is_array($item[$field]))
                    {
                        $return = array_shift($item[$field]);
                    }
                    else
                    {
                        $this->log(LogLevel::WARNING, "'first' selector requires the field '$field' to be an array", $index);
                    }

                    break;
                case 'last':
                    if (is_array($item[$field]))
                    {
                        $return = array_pop($item[$field]);
                    }
                    else
                    {
                        $this->log(LogLevel::WARNING, "'last' selector requires the field '$field' to be an array", $index);
                    }

                    break;
                case 'key':
                    if (!is_array($step['options'] ?? NULL))
                    {
                        $this->logIncompleteConfiguration("'options' is missing for the 'key' selector", $index);
                        break;
                    }

                    if (array_key_exists('key', $step['options']))
                    {
                        $key = $step['options']['key'];
                    }
                    elseif (array_key_exists('field', $step['options'])
                        && is_string($step['options']['field'])
                        && array_key_exists($step['options']['field'], $item)
                    )
                    {
                        $key = $item[$step['options']['field']];
                    }
                    else
                    {
                        $this->logIncompleteConfiguration("Neither 'key' nor 'field' option is defined for the 'key' selector", $index);
                        break;
                    }

                    if (is_object($item[$field]) && is_string($key))
                    {
                        $return = property_exists($item[$field], $key) ? $item[$field]->{$key} : NULL;
                    }

                    if (is_array($item[$field]) && (is_string($key) || is_int($key)))
                    {
                        $return = key_exists($key, $item[$field]) ? $item[$field][$key] : NULL;
                    }

                    break;
                case 'object':
                    $return = NULL;

                    if (!is_array($step['options'] ?? NULL))
                    {
                        $this->logIncompleteConfiguration("'options' is missing for the 'object' selector", $index);
                        break;
                    }

                    if (is_array($item[$field]))
                    {
                        $value = NULL;
                        $key   = NULL;

                        if (array_key_exists('key', $step['options']))
                        {
                            $key = $step['options']['key'];
                        }
                        elseif (is_string($step['options']['field_key'] ?? NULL) && array_key_exists($step['options']['field_key'], $item))
                        {
                            $key = $item[$step['options']['field_key']];
                        }

                        if (array_key_exists('value', $step['options']))
                        {
                            $value = $step['options']['value'];
                        }
                        elseif (is_string($step['options']['field_value'] ?? NULL) && array_key_exists($step['options']['field_value'], $item))
                        {
                            $value = $item[$step['options']['field_value']];
                        }

                        if ($key === NULL)
                        {
                            $this->logIncompleteConfiguration("Neither 'key' nor 'field_key' is defined for the 'object' selector", $index);
                            break;
                        }

                        if ($value === NULL)
                        {
                            $this->logIncompleteConfiguration("Neither 'value' nor 'field_value' is defined for the 'object' selector", $index);
                            break;
                        }

                        foreach ($item[$field] as $object)
                        {
                            if (is_array($object) && (is_string($key) || is_int($key)) && $object[$key] == $value)
                            {
                                $return = $object;
                                break;
                            }
                        }
                    }
                    else
                    {
                        $this->log(LogLevel::WARNING, "'object' selector requires the field '$field' to be an array or an object", $index);
                    }

                    break;
                case 'default':
                default:
                    $return = $item[$field];
                    break;
            }

            /** @var array<mixed>|scalar|object|null $return */
            $item[$field] = $return;

            if (!is_array($step['options'] ?? NULL) || ($step['options']['target'] ?? NULL) !== 'root')
            {
                continue;
            }

            if (!is_array($return))
            {
                $this->log(LogLevel::WARNING, "Cannot replace item with non-array value for field '$field'", $index);
                continue;
            }

            /** @var Item $item */
            $item = $return;
        }

        return $item;
    }

}

?>
