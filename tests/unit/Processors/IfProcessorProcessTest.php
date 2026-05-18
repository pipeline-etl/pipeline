<?php

/**
 * This file contains the IfProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

/**
 * This class contains tests for the IfProcessor class.
 *
 * @covers \Pipeline\Processors\IfProcessor
 */
class IfProcessorProcessTest extends IfProcessorTestCase
{

    /**
     * Unit test data provider for successful conditions.
     *
     * @return array successful conditions
     */
    public static function successfulConditionProvider(): array
    {
        $ops                   = [];
        $ops['strict_equal']   = [ '===', 0 ];
        $ops['loose_equal']    = [ '==', '0' ];
        $ops['loose_inequal']  = [ '!=', 1 ];
        $ops['spaceship']      = [ '<>', 1 ];
        $ops['strict_inequal'] = [ '!==', FALSE ];
        $ops['smaller']        = [ '<', 10 ];
        $ops['greater']        = [ '>', -10 ];
        $ops['smaller_equal']  = [ '<=', 10 ];
        $ops['greater_equal']  = [ '>=', -10 ];
        $ops['regex']          = [ '=~', '/[0-9]/' ];
        $ops['not_regex']      = [ '!~', '/[a-z]/' ];

        return $ops;
    }

    /**
     * Unit test data provider for unsuccessful conditions.
     *
     * @return array unsuccessful conditions
     */
    public static function unsuccessfulConditionProvider(): array
    {
        $ops                   = [];
        $ops['strict_equal']   = [ '===', 1 ];
        $ops['loose_equal']    = [ '==', '1' ];
        $ops['loose_inequal']  = [ '!=', '0' ];
        $ops['spaceship']      = [ '<>', '0' ];
        $ops['strict_inequal'] = [ '!==', 0 ];
        $ops['smaller']        = [ '<', 0 ];
        $ops['greater']        = [ '>', 0 ];
        $ops['smaller_equal']  = [ '<=', -1 ];
        $ops['greater_equal']  = [ '>=', 1 ];
        $ops['regex']          = [ '=~', '/[a-z]/' ];
        $ops['not_regex']      = [ '!~', '/[0-9]/' ];

        return $ops;
    }

    /**
     * Unit test data provider for successful field value conditions.
     *
     * @return array successful field value conditions
     */
    public static function successfulFieldValueConditionProvider(): array
    {
        $ops                   = [];
        $ops['strict_equal']   = [ '===', 'source' ];
        $ops['loose_equal']    = [ '==', 'equal' ];
        $ops['loose_inequal']  = [ '!=', 'not_equal' ];
        $ops['spaceship']      = [ '<>', 'not_equal' ];
        $ops['strict_inequal'] = [ '!==', 'not_strictly_equal' ];
        $ops['smaller']        = [ '<', 'lower' ];
        $ops['greater']        = [ '>', 'greater' ];
        $ops['smaller_equal']  = [ '<=', 'lower_equal' ];
        $ops['greater_equal']  = [ '>=', 'greater_equal' ];
        $ops['regex']          = [ '=~', 'regex_match' ];
        $ops['not_regex']      = [ '!~', 'regex_no_match' ];

        return $ops;
    }

    /**
     * Unit test data provider for unsuccessful field value conditions.
     *
     * @return array unsuccessful field value conditions
     */
    public static function unsuccessfulFieldValueConditionProvider(): array
    {
        $ops                   = [];
        $ops['strict_equal']   = [ '===', 'equal' ];
        $ops['loose_equal']    = [ '==', 'not_equal' ];
        $ops['loose_inequal']  = [ '!=', 'equal' ];
        $ops['spaceship']      = [ '<>', 'equal' ];
        $ops['strict_inequal'] = [ '!==', 'source' ];
        $ops['smaller']        = [ '<', 'greater' ];
        $ops['greater']        = [ '>', 'lower' ];
        $ops['smaller_equal']  = [ '<=', 'greater_equal' ];
        $ops['greater_equal']  = [ '>=', 'lower_equal' ];
        $ops['regex']          = [ '=~', 'regex_no_match' ];
        $ops['not_regex']      = [ '!~', 'regex_match' ];

        return $ops;
    }

    /**
     * Unit test data provider for successful contains conditions.
     *
     * @return array successful contains conditions
     */
    public static function successfulContainsConditionProvider(): array
    {
        $ops   = [];
        $ops[] = [ 'contains', 'in_array' ];
        $ops[] = [ 'not_contains', 'not_in_array' ];
        $ops[] = [ 'contains', 'contains' ];
        $ops[] = [ 'not_contains', 'not_contains' ];

        return $ops;
    }

    /**
     * Unit test data provider for unsuccessful contains conditions.
     *
     * @return array unsuccessful contains conditions
     */
    public static function unsuccessfulContainsConditionProvider(): array
    {
        $ops   = [];
        $ops[] = [ 'contains', 'not_in_array' ];
        $ops[] = [ 'not_contains', 'in_array' ];
        $ops[] = [ 'contains', 'not_contains' ];
        $ops[] = [ 'not_contains', 'contains' ];

        return $ops;
    }

    /**
     * Unit test data provider for successful in-array conditions.
     *
     * @return array successful in-array conditions
     */
    public static function successfulInArrayConditionProvider(): array
    {
        $ops   = [];
        $ops[] = [ 'in', 'source' ];
        $ops[] = [ 'not_in', 'lower' ];

        return $ops;
    }

    /**
     * Unit test data provider for unsuccessful in-array conditions.
     *
     * @return array unsuccessful in-array conditions
     */
    public static function unsuccessfulInArrayConditionProvider(): array
    {
        $ops   = [];
        $ops[] = [ 'in', 'lower_equal' ];
        $ops[] = [ 'not_in', 'source' ];

        return $ops;
    }

    /**
     * Test that process() skips processing with missing 'field' config.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithMissingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'value'      => 0,
            'condition'  => '===',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => "Incomplete configuration: 'field' is missing",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with invalid 'field' config.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithInvalidField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => NULL,
            'value'      => 0,
            'condition'  => '===',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => "Invalid configuration: 'field' is not a string",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with missing comparison value.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithMissingComparisonValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => 'source',
            'condition'  => '===',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => 'Incomplete configuration: No comparison value defined',
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with non-existing field value.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithNonExistingFieldValue(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => 'source',
            'field_value' => 'nonexistent',
            'condition'   => '===',
            'processors'  => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => "Comparison field 'nonexistent' does not exist",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing with missing processors config.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithMissingProcessors(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'     => 'source',
            'value'     => 0,
            'condition' => '===',
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => 'Incomplete configuration: No processor defined',
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs processors with a successful condition.
     *
     * @param string $condition Condition operator
     * @param mixed  $value     Comparison value
     *
     * @dataProvider successfulConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithSuccessfulCondition(string $condition, mixed $value): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => 'source',
            'value'      => $value,
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [], $data)
                     ->andReturn([ 'processed' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed' ], $result);
    }

    /**
     * Test that process() does not run processors with an unsuccessful condition.
     *
     * @param string $condition Condition operator
     * @param mixed  $value     Comparison value
     *
     * @dataProvider unsuccessfulConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnsuccessfulCondition(string $condition, mixed $value): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => 'source',
            'value'      => $value,
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs processors with a successful field value condition.
     *
     * @param string $condition Condition operator
     * @param string $value     Field name to compare against
     *
     * @dataProvider successfulFieldValueConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithSuccessfulFieldValueCondition(string $condition, string $value): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => 'source',
            'field_value' => $value,
            'condition'   => $condition,
            'processors'  => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [], $data)
                     ->andReturn([ 'processed' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed' ], $result);
    }

    /**
     * Test that process() does not run processors with an unsuccessful field value condition.
     *
     * @param string $condition Condition operator
     * @param string $value     Field name to compare against
     *
     * @dataProvider unsuccessfulFieldValueConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnsuccessfulFieldValueCondition(string $condition, string $value): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => 'source',
            'field_value' => $value,
            'condition'   => $condition,
            'processors'  => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs processors with a successful contains condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider successfulContainsConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithSuccessfulContainsCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => $field,
            'value'      => 0,
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [], $data)
                     ->andReturn([ 'processed' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed' ], $result);
    }

    /**
     * Test that process() does not run processors with an unsuccessful contains condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider unsuccessfulContainsConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnsuccessfulContainsCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => $field,
            'value'      => 0,
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs processors with a successful in-array condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider successfulInArrayConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithSuccessfulInArrayCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => $field,
            'value'      => [ 0, 1 ],
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [], $data)
                     ->andReturn([ 'processed' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed' ], $result);
    }

    /**
     * Test that process() does not run processors with an unsuccessful in-array condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider unsuccessfulInArrayConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnsuccessfulInArrayCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => $field,
            'value'      => [ 0, 1 ],
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() logs invalid configuration when in/not_in value is not an array.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider unsuccessfulInArrayConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithNonArrayValueForInCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => $field,
            'value'      => 1,
            'condition'  => $condition,
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => 'Invalid configuration: Comparison value is not an array',
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs processors with a successful field value contains condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider successfulContainsConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithSuccessfulFieldValueContainsCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => $field,
            'field_value' => 'source',
            'condition'   => $condition,
            'processors'  => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock', [], $data)
                     ->andReturn([ 'processed' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed' ], $result);
    }

    /**
     * Test that process() does not run processors with an unsuccessful field value contains condition.
     *
     * @param string $condition Condition operator
     * @param string $field     Field name to check
     *
     * @dataProvider unsuccessfulContainsConditionProvider
     * @covers       \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnsuccessfulFieldValueContainsCondition(string $condition, string $field): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => $field,
            'field_value' => 'source',
            'condition'   => $condition,
            'processors'  => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() skips processing when the field does not exist in the item.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithNonExistingField(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => 'nonexistent',
            'value'      => 0,
            'condition'  => '===',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => "Field 'nonexistent' does not exist",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() logs invalid configuration for an unknown condition.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithUnknownCondition(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'      => 'source',
            'value'      => 0,
            'condition'  => 'invalid',
            'processors' => [[ 'mock' => [] ]],
        ];

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'IfProcessor',
                         'index'   => '',
                         'message' => "Invalid configuration: Unknown condition 'invalid'",
                     ]);

        $result = $this->class->process($data, $config);
        $this->assertSame($data, $result);
    }

    /**
     * Test that process() runs multiple processors sequentially.
     *
     * @covers \Pipeline\Processors\IfProcessor::process
     */
    public function testProcessWithMultipleProcessors(): void
    {
        $data = json_decode(file_get_contents(TEST_STATICS . '/Processors/if_item.json'), TRUE);

        $config = [
            'field'       => 'source',
            'field_value' => 'source',
            'condition'   => '===',
            'processors'  => [
                [ 'mock1' => [ 'step' => 1 ] ],
                [ 'mock2' => [ 'step' => 2 ] ],
            ],
        ];

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock1', [ 'step' => 1 ], $data)
                     ->andReturn([ 'processed 1' ]);

        $this->runner->shouldReceive('run')
                     ->once()
                     ->with('mock2', [ 'step' => 2 ], [ 'processed 1' ])
                     ->andReturn([ 'processed 2' ]);

        $result = $this->class->process($data, $config);
        $this->assertSame([ 'processed 2' ], $result);
    }

}

?>
