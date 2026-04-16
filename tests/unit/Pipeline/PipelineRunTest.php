<?php

/**
 * This file contains the PipelineRunTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Pipeline;

/**
 * This class contains tests for the run() method of the Pipeline class.
 *
 * @covers \Pipeline\Pipeline
 */
class PipelineRunTest extends PipelineTestCase
{

    /**
     * Test that run() returns the processed item when the processor exists.
     *
     * @covers \Pipeline\Pipeline::run
     */
    public function testRunReturnsProcessedItemWhenProcessorExists(): void
    {
        $item   = [ 'key1' => 'value1' ];
        $config = [ 'field' => 'key1' ];
        $result = [ 'key1' => 'processed' ];

        $processor = $this->getProcessorMock($result);

        $this->locator->shouldReceive('getProcessor')
                      ->once()
                      ->with('foo')
                      ->andReturn($processor);

        $this->assertSame($result, $this->class->run('foo', $config, $item));
    }

    /**
     * Test that run() returns the original item when the processor does not exist.
     *
     * @covers \Pipeline\Pipeline::run
     */
    public function testRunReturnsOriginalItemWhenProcessorDoesNotExist(): void
    {
        $item   = [ 'key1' => 'value1' ];
        $config = [ 'field' => 'key1' ];

        $this->locator->shouldReceive('getProcessor')
                      ->once()
                      ->with('nonexistent')
                      ->andReturn(NULL);

        $this->assertSame($item, $this->class->run('nonexistent', $config, $item));
    }

}

?>
