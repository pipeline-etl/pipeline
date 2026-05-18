<?php

/**
 * This file contains the KvSplitPreprocessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Preprocessors;

/**
 * This class contains tests for the KvSplitPreprocessor class.
 *
 * @covers \Pipeline\Preprocessors\KvSplitPreprocessor
 */
class KvSplitPreprocessorProcessTest extends KvSplitPreprocessorTestCase
{

    /**
     * Test process() with an empty config.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithEmptyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => 'Incomplete configuration: No configuration defined!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config missing 'field'.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithMissingFieldConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'key'   => 'position',
            'value' => 'tag',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Incomplete configuration: 'field' not defined!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a non-string 'field' config.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithNonStringFieldConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'field' => 123,
            'key'   => 'position',
            'value' => 'tag',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Invalid configuration: 'field' is not a string!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config missing 'key'.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithMissingKeyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'field' => 'tags',
            'value' => 'tag',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Incomplete configuration: 'key' not defined!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a non-string 'key' config.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithNonStringKeyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'field' => 'tags',
            'key'   => 123,
            'value' => 'tag',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Invalid configuration: 'key' is not a string!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config missing 'value'.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithMissingValueConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'field' => 'tags',
            'key'   => 'position',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Incomplete configuration: 'value' not defined!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a non-string 'value' config.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithNonStringValueConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $config = [
            'field' => 'tags',
            'key'   => 'position',
            'value' => 123,
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'KvSplitPreprocessor',
                         'index' => '',
                         'message' => "Invalid configuration: 'value' is not a string!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() splits an array field into key-value pairs.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithArrayField(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_array_result.json'), TRUE);

        $config = [
            'field' => 'tags',
            'key'   => 'position',
            'value' => 'tag',
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test process() splits an object field into key-value pairs.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithObjectField(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_object.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_object_result.json'), TRUE);

        $input[0]['tags'] = (object) $input[0]['tags'];

        $expected[0]['tags'] = (object) $expected[0]['tags'];
        $expected[1]['tags'] = (object) $expected[1]['tags'];

        $config = [
            'field' => 'tags',
            'key'   => 'position',
            'value' => 'tag',
        ];

        $results = $this->class->process($input, $config);
        $this->assertEquals($expected, $results);
    }

    /**
     * Test process() sets key and value to NULL for a scalar field.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithScalarField(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_scalar.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_scalar_result.json'), TRUE);

        $config = [
            'field' => 'tags',
            'key'   => 'position',
            'value' => 'tag',
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test process() sets key and value to NULL for a null field.
     *
     * @covers \Pipeline\Preprocessors\KvSplitPreprocessor::process
     */
    public function testProcessWithNullField(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_null.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/kvsplit_null_result.json'), TRUE);

        $config = [
            'field' => 'tags',
            'key'   => 'position',
            'value' => 'tag',
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

}

?>
