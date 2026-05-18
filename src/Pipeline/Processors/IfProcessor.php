<?php

/**
 * This file contains the IfProcessor class.
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
 * If Pipeline Processor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class IfProcessor extends FlowControlProcessor implements ProcessorInterface
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
        if (!array_key_exists('value', $config) && !array_key_exists('field_value', $config))
        {
            $this->logIncompleteConfiguration('No comparison value defined');

            return $item;
        }

        if (array_key_exists('field_value', $config) && !array_key_exists($config['field_value'], $item))
        {
            $this->log(LogLevel::WARNING, "Comparison field '{$config['field_value']}' does not exist");

            return $item;
        }

        if (!array_key_exists('processors', $config))
        {
            $this->logIncompleteConfiguration('No processor defined');

            return $item;
        }

        $field     = $config['field'];
        $value     = array_key_exists('field_value', $config) ? $item[$config['field_value']] : $config['value'];
        $condition = $config['condition'] ?? '===';

        if (!array_key_exists($field, $item))
        {
            $this->log(LogLevel::WARNING, "Field '$field' does not exist");

            return $item;
        }

        switch ($condition)
        {
            case '==':
                $execute = $item[$field] == $value;
                break;
            case '!=':
            case '<>':
                $execute = $item[$field] != $value;
                break;
            case '!==':
                $execute = $item[$field] !== $value;
                break;
            case '<':
                $execute = $item[$field] < $value;
                break;
            case '>':
                $execute = $item[$field] > $value;
                break;
            case '<=':
                $execute = $item[$field] <= $value;
                break;
            case '>=':
                $execute = $item[$field] >= $value;
                break;
            case '=~':
                $execute = preg_match($value, $item[$field]) === 1;
                break;
            case '!~':
                $execute = preg_match($value, $item[$field]) === 0;
                break;
            case 'contains':
                if (is_array($item[$field]))
                {
                    $execute = in_array($value, $item[$field]);
                }
                else
                {
                    $execute = str_contains($item[$field], (string) $value);
                }
                break;
            case 'not_contains':
                if (is_array($item[$field]))
                {
                    $execute = !in_array($value, $item[$field]);
                }
                else
                {
                    $execute = !str_contains($item[$field], (string) $value);
                }
                break;
            case 'in':
                if (is_array($value))
                {
                    $execute = in_array($item[$field], $value);
                }
                else
                {
                    $this->logInvalidConfiguration('Comparison value is not an array');
                    $execute = FALSE;
                }
                break;
            case 'not_in':
                if (is_array($value))
                {
                    $execute = !in_array($item[$field], $value);
                }
                else
                {
                    $this->logInvalidConfiguration('Comparison value is not an array');
                    $execute = FALSE;
                }
                break;
            case '===':
                $execute = $item[$field] === $value;
                break;
            default:
                $this->logInvalidConfiguration("Unknown condition '$condition'");
                $execute = FALSE;
                break;
        }

        if ($execute !== TRUE)
        {
            return $item;
        }

        foreach ($config['processors'] as $processor)
        {
            [ $name, $stepConfig ] = [ key($processor), current($processor) ];
            $item                  = $this->runner->run($name, $stepConfig, $item);
        }

        return $item;
    }

}

?>
