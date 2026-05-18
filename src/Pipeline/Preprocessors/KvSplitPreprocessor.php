<?php

/**
 * This file contains the KvSplitPreprocessor class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Preprocessors;

use Pipeline\Common\Node;
use Pipeline\Common\PreprocessorInterface;
use Pipeline\Common\ProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Key Value Split Pipeline Preprocessor.
 *
 * @phpstan-import-type Item from Node
 * @phpstan-import-type ProcessorConfig from ProcessorInterface
 */
class KvSplitPreprocessor extends Node implements PreprocessorInterface
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
            $this->logIncompleteConfiguration('No configuration defined!');
            return $data;
        }

        /**
         * https://github.com/phpstan/phpstan/issues/12533
         * @var mixed $field
         */
        $field = $config['field'] ?? NULL;

        /**
         * https://github.com/phpstan/phpstan/issues/12533
         * @var mixed $keyField
         */
        $keyField = $config['key'] ?? NULL;

        /**
         * https://github.com/phpstan/phpstan/issues/12533
         * @var mixed $valueField
         */
        $valueField = $config['value'] ?? NULL;

        if ($field === NULL)
        {
            $this->logIncompleteConfiguration("'field' not defined!");
            return $data;
        }

        if (!is_string($field))
        {
            $this->logInvalidConfiguration("'field' is not a string!");
            return $data;
        }

        if ($keyField === NULL)
        {
            $this->logIncompleteConfiguration("'key' not defined!");
            return $data;
        }

        if (!is_string($keyField))
        {
            $this->logInvalidConfiguration("'key' is not a string!");
            return $data;
        }

        if ($valueField === NULL)
        {
            $this->logIncompleteConfiguration("'value' not defined!");
            return $data;
        }

        if (!is_string($valueField))
        {
            $this->logInvalidConfiguration("'value' is not a string!");
            return $data;
        }

        $output = [];

        foreach ($data as $item)
        {
            if (is_array($item[$field]))
            {
                $entries = $item[$field];
            }
            elseif (is_object($item[$field]))
            {
                $entries = get_object_vars($item[$field]);
            }
            else
            {
                $new = $item;

                $new[$keyField]   = NULL;
                $new[$valueField] = NULL;

                $output[] = $new;

                continue;
            }

            foreach ($entries as $key => $value)
            {
                $new = $item;

                $new[$keyField]   = $key;
                $new[$valueField] = $value;

                $output[] = $new;
            }
        }

        /** @var Item[] $output */
        return $output;
    }

}

?>
