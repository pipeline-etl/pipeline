<?php

/**
 * This file contains the ForeachProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

/**
 * This class contains tests for the ForeachProcessor class.
 *
 * @covers \Pipeline\Processors\ForeachProcessor
 */
class ForeachProcessorProcessTest extends ForeachProcessorTestCase
{

    /**
     * Test that process() skips processing with missing 'field' config.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithMissingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'processor' => [ 'mock' => [] ],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ForeachProcessor',
                         'index'   => '',
                         'message' => "Incomplete configuration: 'field' is missing",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with invalid 'field' config.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithInvalidField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'     => NULL,
            'processor' => [ 'mock' => [] ],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ForeachProcessor',
                         'index'   => '',
                         'message' => "Invalid configuration: 'field' is not a string",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with missing processor config.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithMissingProcessors(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field' => 'variants',
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ForeachProcessor',
                         'index'   => '',
                         'message' => 'Incomplete configuration: No processor defined',
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing when the field does not exist in the item.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithNonExistingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'     => 'nonexistent',
            'processor' => [ 'mock' => [] ],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ForeachProcessor',
                         'index'   => '',
                         'message' => "Field 'nonexistent' does not exist",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() returns item unchanged when field value is null.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithNullValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'     => 'category',
            'processor' => [ 'mock' => [] ],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() sets field to null when value is not an array.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithNonArrayValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'     => 'name',
            'processor' => [ 'mock' => [] ],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'ForeachProcessor',
                         'index'   => '',
                         'message' => "Field 'name' is not an array. Setting value to NULL",
                     ]);

        $result = $this->class->process($data, $config);

        $data['name'] = NULL;

        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs a single processor on each sub-item.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithSingleProcessor(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'     => 'variants',
            'processor' => [ 'mock' => [ 'size' => 'M' ] ],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [ 'size' => 'M' ], [ 'sku' => 'SKU-001', 'color' => 'red' ])
                     ->andReturn([ 'sku' => 'SKU-001', 'color' => 'red', 'size' => 'M' ]);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [ 'size' => 'M' ], [ 'sku' => 'SKU-002', 'color' => 'blue' ])
                     ->andReturn([ 'sku' => 'SKU-002', 'color' => 'blue', 'size' => 'M' ]);

        $result = $this->class->process($data, $config);

        $data['variants'] = [
            [ 'sku' => 'SKU-001', 'color' => 'red', 'size' => 'M' ],
            [ 'sku' => 'SKU-002', 'color' => 'blue', 'size' => 'M' ],
        ];

        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs multiple processors sequentially on each sub-item.
     *
     * @covers \Pipeline\Processors\ForeachProcessor::process
     */
    public function testProcessWithMultipleProcessors(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/foreach_item.json'), TRUE);

        $config = [
            'field'      => 'variants',
            'processors' => [
                [ 'mock1' => [ 'step' => 1 ] ],
                [ 'mock2' => [ 'step' => 2 ] ],
            ],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock1', [ 'step' => 1 ], [ 'sku' => 'SKU-001', 'color' => 'red' ])
                     ->andReturn([ 'sku' => 'SKU-001', 'color' => 'red', 'processed1' => TRUE ]);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock2', [ 'step' => 2 ], [ 'sku' => 'SKU-001', 'color' => 'red', 'processed1' => TRUE ])
                     ->andReturn([ 'sku' => 'SKU-001', 'color' => 'red', 'processed1' => TRUE, 'processed2' => TRUE ]);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock1', [ 'step' => 1 ], [ 'sku' => 'SKU-002', 'color' => 'blue' ])
                     ->andReturn([ 'sku' => 'SKU-002', 'color' => 'blue', 'processed1' => TRUE ]);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock2', [ 'step' => 2 ], [ 'sku' => 'SKU-002', 'color' => 'blue', 'processed1' => TRUE ])
                     ->andReturn([ 'sku' => 'SKU-002', 'color' => 'blue', 'processed1' => TRUE, 'processed2' => TRUE ]);

        $result = $this->class->process($data, $config);

        $data['variants'] = [
            [ 'sku' => 'SKU-001', 'color' => 'red', 'processed1' => TRUE, 'processed2' => TRUE ],
            [ 'sku' => 'SKU-002', 'color' => 'blue', 'processed1' => TRUE, 'processed2' => TRUE ],
        ];

        $this->assertSame($data, $result);
    }

}

?>
