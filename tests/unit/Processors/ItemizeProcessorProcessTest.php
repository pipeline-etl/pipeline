<?php

/**
 * This file contains the ItemizeProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

/**
 * This class contains tests for the ItemizeProcessor class.
 *
 * @covers \Pipeline\Processors\ItemizeProcessor
 */
class ItemizeProcessorProcessTest extends ItemizeProcessorTestCase
{

    /**
     * Test that process() skips processing with missing 'fields' config.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessWithMissingFieldsConfig(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ItemizeProcessor',
                         'index'   => '',
                         'message' => "Incomplete configuration: 'fields' is missing",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing when 'fields' config is not an array.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessWithInvalidFieldsConfig(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => 'invalid' ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ItemizeProcessor',
                         'index'   => '',
                         'message' => "Invalid configuration: 'fields' is not an array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips a field that does not exist in the item.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSkipsNonExistingField(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'nonexistent' ] ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ItemizeProcessor',
                         'index'   => '',
                         'message' => "Skip 'nonexistent', field does not exist",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips a field that is not an array.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSkipsNonArrayField(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'stock' ] ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ItemizeProcessor',
                         'index'   => '',
                         'message' => "Skip 'stock', field is not an array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips a field that is not an indexed array.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSkipsAssociativeArrayField(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'metadata' ] ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ItemizeProcessor',
                         'index'   => '',
                         'message' => "Skip 'metadata', field is not an indexed array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() succeeds when the field is an empty array.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSucceedsWithEmptyArray(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'tags' ] ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() fills missing keys with null and sorts keys.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessItemizesObjectArray(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'variants' ] ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);

        $expected             = $data;
        $expected['variants'] = [
            [
                'color'  => 'red',
                'size'   => 'M',
                'weight' => NULL,
            ],
            [
                'color'  => 'blue',
                'size'   => NULL,
                'weight' => 150,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that process() skips non-string field entries in the config.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSkipsNonStringFieldEntry(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 0, NULL ] ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() converts object sub-items to arrays before itemizing.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessConvertsObjectSubItems(): void
    {
        $data   = (array) json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_object_item.json'));
        $config = [ 'fields' => [ 'variants' ] ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);

        $expected             = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_object_item.json'), TRUE);
        $expected['variants'] = [
            [
                'color'  => 'red',
                'size'   => 'M',
                'weight' => NULL,
            ],
            [
                'color'  => 'blue',
                'size'   => NULL,
                'weight' => 150,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test that process() skips scalar sub-items in an indexed array.
     *
     * @covers \Pipeline\Processors\ItemizeProcessor::process
     */
    public function testProcessSkipsScalarSubItems(): void
    {
        $data   = json_decode(file_get_contents(TEST_STATICS . '/Processors/itemize_item.json'), TRUE);
        $config = [ 'fields' => [ 'scores' ] ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

}

?>
