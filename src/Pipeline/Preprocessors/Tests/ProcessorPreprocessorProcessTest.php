<?php

/**
 * This file contains the ProcessorProcessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Preprocessors\Tests;

/**
 * This class contains tests for the ProcessorProcessor class.
 *
 * @covers \Pipeline\Preprocessors\ProcessorPreprocessor
 */
class ProcessorPreprocessorProcessTest extends ProcessorPreprocessorTestCase
{

    /**
     * Test process() with an empty config.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWithEmptyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [];

        $this->class->link($this->runner);

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '',
                         'message' => 'Incomplete configuration: No processors defined to run!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() when the provided config is not a list.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWhenConfigIsNotList(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [ 'value' => [ 'hello' => 'world' ]];

        $this->class->link($this->runner);

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '',
                         'message' => 'Invalid configuration: Processors not defined in a list!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid processor identifier.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWithInvalidProcessorIdentifier(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [[ [ 'hello' => 'world' ]]];

        $this->class->link($this->runner);

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '[@0]',
                         'message' => 'Invalid configuration: Processor identifier is not a string!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid processor config.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWithInvalidProcessorConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [[ 'hello' => 'world' ]];

        $this->class->link($this->runner);

        $this->runner->shouldReceive('run')
                     ->never();

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '[@0]',
                         'message' => "Invalid configuration: Configuration for processor 'hello' is not an array!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test that process() works correctly when adding a Processor.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessRunsSingleProcessor(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [ [ 'value' => [ 'hello' => 'world' ]] ];

        $expected = [];

        $this->class->link($this->runner);

        foreach ($input as $item)
        {
            $this->runner->shouldReceive('run')
                         ->once()
                         ->with('value', [ 'hello' => 'world' ], $item)
                         ->andReturnUsing(fn(string $identifier, array $stepConfig, array $data) => $data + $stepConfig);

            $expected[] = $item + [ 'hello' => 'world' ];
        }

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() works correctly when adding a Processor.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessRunsMultipleProcessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [
            [
                'value' => [
                    'hello' => 'world',
                ],
            ],
            [
                'value' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $expected = [];

        $this->class->link($this->runner);

        foreach ($input as $item)
        {
            $this->runner->shouldReceive('run')
                         ->with('value', [ 'hello' => 'world' ], $item)
                         ->andReturnUsing(fn(string $identifier, array $stepConfig, array $data) => $data + $stepConfig);

            $item += [ 'hello' => 'world' ];

            $this->runner->shouldReceive('run')
                         ->with('value', [ 'foo' => 'bar' ], $item)
                         ->andReturnUsing(fn(string $identifier, array $stepConfig, array $data) => $data + $stepConfig);

            $expected[] = $item + [ 'foo' => 'bar' ];
        }

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test process() with a config containing an invalid processor identifier.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWithInvalidProcessorIdentifierAndMultipleProcessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [
            [
                'value' => [
                    'hello' => 'world',
                ],
            ],
            [
                [
                    'foo' => 'bar',
                ],
            ],
        ];

        $expected = [];

        $this->class->link($this->runner);

        foreach ($input as $item)
        {
            $this->runner->shouldReceive('run')
                         ->with('value', [ 'hello' => 'world' ], $item)
                         ->andReturnUsing(fn(string $identifier, array $stepConfig, array $data) => $data + $stepConfig);

            $expected[] = $item + [ 'hello' => 'world' ];
        }

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '[@1]',
                         'message' => 'Invalid configuration: Processor identifier is not a string!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test process() with a config containing an invalid processor config.
     *
     * @covers \Pipeline\Preprocessors\ProcessorPreprocessor::process
     */
    public function testProcessWithInvalidProcessorConfigAndMultipleProcessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/simple_data.json'), TRUE);
        $config = [
            [
                'hello' => 'world',
            ],
            [
                'value' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $expected = [];

        $this->class->link($this->runner);

        foreach ($input as $item)
        {
            $this->runner->shouldReceive('run')
                         ->with('value', [ 'foo' => 'bar' ], $item)
                         ->andReturnUsing(fn(string $identifier, array $stepConfig, array $data) => $data + $stepConfig);

            $expected[] = $item + [ 'foo' => 'bar' ];
        }

        $this->logger->expects($this->once())
                     ->method('log')
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'ProcessorPreprocessor',
                         'index' => '[@0]',
                         'message' => "Invalid configuration: Configuration for processor 'hello' is not an array!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

}

?>
