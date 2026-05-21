<?php

/**
 * This file contains the SelectProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

/**
 * This class contains tests for the SelectProcessor class.
 *
 * @covers \Pipeline\Processors\SelectProcessor
 */
class SelectProcessorProcessTest extends SelectProcessorTestCase
{

    /**
     * Test that process() skips non-array step entries.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessSkipsNonArrayStep(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            'not-an-array',
            [
                'field' => 'name',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() returns early with missing 'field' config.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithMissingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'selector' => 'first',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: 'field' is missing",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() returns early with invalid 'field' config.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithInvalidField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field' => 123,
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Invalid configuration: 'field' is not a string",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() returns early when the field does not exist in the item.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithNonExistingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field' => 'nonexistent',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Field 'nonexistent' does not exist in the item",
                     ]);

        $result = $this->class->process($data, $config);

        $expected                = $data;
        $expected['nonexistent'] = NULL;

        $this->assertSame($expected, $result);
    }

    /**
     * Test that process() uses the default selector when none is specified.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithDefaultSelector(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field' => 'name',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() selects the first element from an array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessFirstSelector(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'tags',
                'selector' => 'first',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('alpha', $result['tags']);
    }

    /**
     * Test that process() logs a warning when the 'first' selector is used on a non-array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessFirstSelectorWithNonArrayField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'name',
                'selector' => 'first',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "'first' selector requires the field 'name' to be an array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['name']);
    }

    /**
     * Test that process() selects the last element from an array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessLastSelector(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'tags',
                'selector' => 'last',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('gamma', $result['tags']);
    }

    /**
     * Test that process() logs a warning when the 'last' selector is used on a non-array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessLastSelectorWithNonArrayField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'name',
                'selector' => 'last',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "'last' selector requires the field 'name' to be an array",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['name']);
    }

    /**
     * Test that process() logs incomplete config when 'key' selector has no options.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithMissingOptions(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'metadata',
                'selector' => 'key',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: 'options' is missing for the 'key' selector",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['metadata']);
    }

    /**
     * Test that process() selects by key from an array field using a static key option.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithKeyOption(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'metadata',
                'selector' => 'key',
                'options'  => [
                    'key' => 'version',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('1.0', $result['metadata']);
    }

    /**
     * Test that process() selects by key from an array field using a field reference.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithFieldOption(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'metadata',
                'selector' => 'key',
                'options'  => [
                    'field' => 'lookup_key',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('1.0', $result['metadata']);
    }

    /**
     * Test that process() logs incomplete config when neither 'key' nor 'field' is defined.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithMissingKeyAndField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'metadata',
                'selector' => 'key',
                'options'  => [],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: Neither 'key' nor 'field' option is defined for the 'key' selector",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['metadata']);
    }

    /**
     * Test that process() returns NULL when the key does not exist in the array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithNonExistingKey(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'metadata',
                'selector' => 'key',
                'options'  => [
                    'key' => 'nonexistent',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['metadata']);
    }

    /**
     * Test that process() selects a property from an object field using the 'key' selector.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithObjectField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $data['object_field'] = (object) [ 'version' => '2.0', 'author' => 'admin' ];

        $config = [
            [
                'field'    => 'object_field',
                'selector' => 'key',
                'options'  => [
                    'key' => 'version',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('2.0', $result['object_field']);
    }

    /**
     * Test that process() returns NULL when the property does not exist on an object field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessKeySelectorWithObjectFieldNonExistingProperty(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $data['object_field'] = (object) [ 'version' => '2.0' ];

        $config = [
            [
                'field'    => 'object_field',
                'selector' => 'key',
                'options'  => [
                    'key' => 'nonexistent',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['object_field']);
    }

    /**
     * Test that process() logs incomplete config when 'object' selector has no options.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithMissingOptions(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: 'options' is missing for the 'object' selector",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['entries']);
    }

    /**
     * Test that process() logs a warning when the 'object' selector is used on a non-array field.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithNonArrayField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'name',
                'selector' => 'object',
                'options'  => [
                    'key'   => 'type',
                    'value' => 'A',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "'object' selector requires the field 'name' to be an array or an object",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['name']);
    }

    /**
     * Test that process() finds a matching object using static key and value options.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithKeyAndValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'key'   => 'type',
                    'value' => 'B',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'type' => 'B', 'value' => 20 ], $result['entries']);
    }

    /**
     * Test that process() finds a matching object using field_key and field_value references.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithFieldKeyAndFieldValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'field_key'   => 'match_field',
                    'field_value' => 'match_type',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'type' => 'B', 'value' => 20 ], $result['entries']);
    }

    /**
     * Test that process() logs incomplete config when 'object' selector has no key defined.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithMissingKey(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'value' => 'B',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: Neither 'key' nor 'field_key' is defined for the 'object' selector",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['entries']);
    }

    /**
     * Test that process() logs incomplete config when 'object' selector has no value defined.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithMissingValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'key' => 'type',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Incomplete configuration: Neither 'value' nor 'field_value' is defined for the 'object' selector",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['entries']);
    }

    /**
     * Test that process() returns NULL when no matching object is found.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessObjectSelectorWithNoMatch(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'key'   => 'type',
                    'value' => 'Z',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame(NULL, $result['entries']);
    }

    /**
     * Test that process() replaces the item with the selected value when target is 'root'.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithTargetRoot(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'entries',
                'selector' => 'object',
                'options'  => [
                    'key'    => 'type',
                    'value'  => 'B',
                    'target' => 'root',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'type' => 'B', 'value' => 20 ], $result);
    }

    /**
     * Test that process() logs a warning when target is 'root' but the return value is not an array.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessWithTargetRootNonArrayReturn(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'   => 'name',
                'options' => [
                    'target' => 'root',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'SelectProcessor',
                         'index'   => '[@0]',
                         'message' => "Cannot replace item with non-array value for field 'name'",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() processes multiple steps sequentially.
     *
     * @covers \Pipeline\Processors\SelectProcessor::process
     */
    public function testProcessMultipleSteps(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/select_item.json'), TRUE);

        $config = [
            [
                'field'    => 'tags',
                'selector' => 'first',
            ],
            [
                'field'    => 'metadata',
                'selector' => 'key',
                'options'  => [
                    'key' => 'version',
                ],
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame('alpha', $result['tags']);
        $this->assertSame('1.0', $result['metadata']);
    }

}

?>
