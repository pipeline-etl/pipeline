<?php

/**
 * This file contains the ValueProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Processors\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains tests for the ValueProcessor class.
 *
 * @covers Pipeline\Processors\ValueProcessor
 */
class ValueProcessorProcessTest extends ValueProcessorTestCase
{

    use PsrLoggerTestTrait;

    /**
     * Test process() with an empty configuration.
     *
     * @covers \Pipeline\Processors\ValueProcessor::process
     */
    public function testProcessWithEmptyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Processors/simple_item.json'), TRUE);
        $config = [];

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ValueProcessor',
                         'index' => '',
                         'message' => 'Incomplete configuration: No values defined to set!',
                     ]);

        $result = $this->class->process($input, $config);

        $this->assertSame($input, $result);
    }

    /**
     * Test that process() adds a non-existing value.
     *
     * @covers \Pipeline\Processors\ValueProcessor::process
     */
    public function testProcessAddsValue(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Processors/simple_item.json'), TRUE);
        $config = [ 'fieldH' => 'value' ];

        $result = $this->class->process($input, $config);

        $this->assertArrayNotEmpty($result);
        $this->assertArrayHasKey('fieldA', $result);
        $this->assertArrayHasKey('fieldB', $result);
        $this->assertArrayHasKey('fieldC', $result);
        $this->assertArrayHasKey('fieldD', $result);
        $this->assertArrayHasKey('fieldE', $result);
        $this->assertArrayHasKey('fieldF', $result);
        $this->assertArrayHasKey('fieldG', $result);
        $this->assertArrayHasKey('fieldH', $result);
        $this->assertEquals('value', $result['fieldH']);
    }

    /**
     * Test that process() does not overwrite existing values.
     *
     * @covers \Pipeline\Processors\ValueProcessor::process
     */
    public function testProcessDoesNotOverwriteValue(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Processors/simple_item.json'), TRUE);
        $config = [ 'fieldG' => 'value' ];

        $result = $this->class->process($input, $config);

        $this->assertArrayNotEmpty($result);
        $this->assertArrayHasKey('fieldA', $result);
        $this->assertArrayHasKey('fieldB', $result);
        $this->assertArrayHasKey('fieldC', $result);
        $this->assertArrayHasKey('fieldD', $result);
        $this->assertArrayHasKey('fieldE', $result);
        $this->assertArrayHasKey('fieldF', $result);
        $this->assertArrayHasKey('fieldG', $result);
        $this->assertNull($result['fieldG']);
    }

}

?>
