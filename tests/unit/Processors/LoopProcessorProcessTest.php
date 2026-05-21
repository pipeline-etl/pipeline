<?php

/**
 * This file contains the LoopProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

/**
 * This class contains tests for the LoopProcessor class.
 *
 * @covers \Pipeline\Processors\LoopProcessor
 */
class LoopProcessorProcessTest extends LoopProcessorTestCase
{

    /**
     * Test that process() skips processing with missing 'field' config.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessWithMissingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'LoopProcessor',
                         'index'   => '',
                         'message' => "Incomplete configuration: 'field' is missing",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with invalid 'field' config.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessWithInvalidField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => NULL,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'LoopProcessor',
                         'index'   => '',
                         'message' => "Invalid configuration: 'field' is not a string",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing when the field does not exist in the item.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessWithNonExistingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'nonexistent',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'LoopProcessor',
                         'index'   => '',
                         'message' => "Field 'nonexistent' does not exist",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing when the field is not an array.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessWithNonArrayField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'name',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'LoopProcessor',
                         'index'   => '',
                         'message' => "Field 'name' is not an array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with missing processors config.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessWithMissingProcessors(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field' => 'slots',
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'LoopProcessor',
                         'index'   => '',
                         'message' => 'Incomplete configuration: No processors defined',
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs a processor for each value in the field.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessRunsProcessorForEachValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'slots',
            'processors' => [
                [ 'mock' => [] ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $this->runner->shouldReceive('run')
                     ->times(2)
                     ->with('mock', [], $data)
                     ->andReturn($data);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() replaces LOOP-INDEX in select processor options with the loop index.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessReplacesLoopIndexInSelectOptions(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'slots',
            'processors' => [
                [
                    'select' => [
                        [
                            'options' => [
                                'key' => 'LOOP-INDEX',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('select', [[ 'options' => [ 'key' => 0 ] ]], $data)
                     ->andReturn($data);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('select', [[ 'options' => [ 'key' => 1 ] ]], $data)
                     ->andReturn($data);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips non-array processor entries.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessSkipsNonArrayProcessor(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'slots',
            'processors' => [ 'invalid', [ 'mock' => [] ] ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $this->runner->shouldReceive('run')
                     ->times(2)
                     ->with('mock', [], $data)
                     ->andReturn($data);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips select entries that don't have the LOOP-INDEX pattern.
     *
     * @covers \Pipeline\Processors\LoopProcessor::process
     */
    public function testProcessSkipsSelectEntriesWithoutLoopIndex(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/loop_item.json'), TRUE);

        $config = [
            'field'      => 'slots',
            'processors' => [
                [
                    'select' => [
                        [
                            'options' => [
                                'key' => 'other',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $this->runner->shouldReceive('run')
                     ->times(2)
                     ->with('select', [[ 'options' => [ 'key' => 'other' ] ]], $data)
                     ->andReturn($data);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

}

?>
