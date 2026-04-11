<?php

/**
 * This file contains the DefaultParserProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Parsers;

use Mockery;
use Pipeline\Common\Node;
use Pipeline\Common\PreprocessorInterface;
use Pipeline\Common\ProcessorInterface;

/**
 * This class contains tests for the DefaultParser class.
 *
 * @covers \Pipeline\Parsers\DefaultParser
 */
class DefaultParserProcessTest extends DefaultParserTestCase
{

    /**
     * Test process() with an empty config.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithEmptyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() when the preprocessors config is not a list.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithPreprocessorsNotAList(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [ 'filter' => [ 'field' => 'fieldA' ] ],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '',
                         'message' => 'Invalid configuration: Preprocessors not defined in a list!',
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() when the processors config is not a list.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithProcessorsNotAList(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'processors' => [ 'value' => [ 'hello' => 'world' ] ],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '',
                         'message' => 'Invalid configuration: Processors not defined in a list!',
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid preprocessor identifier.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithInvalidPreprocessorIdentifier(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [[ [ 'hello' => 'world' ] ]],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '[@0]',
                         'message' => 'Invalid configuration: Preprocessor identifier is not a string!',
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid preprocessor config.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithInvalidPreprocessorConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [[ 'hello' => 'world' ]],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '[@0]',
                         'message' => "Invalid configuration: Configuration for preprocessor 'hello' is not an array!",
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid processor identifier.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithInvalidProcessorIdentifier(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'processors' => [[ [ 'hello' => 'world' ] ]],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '[@0]',
                         'message' => 'Invalid configuration: Processor identifier is not a string!',
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config containing an invalid processor config.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessWithInvalidProcessorConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'processors' => [[ 'hello' => 'world' ]],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class'   => 'DefaultParser',
                         'index'   => '[@0]',
                         'message' => "Invalid configuration: Configuration for processor 'hello' is not an array!",
                     ]);

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test that process() runs a single preprocessor on the full dataset.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessRunsSinglePreprocessor(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [
                [ 'filter' => [ 'field' => 'fieldA' ] ],
            ],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $preprocessor = Mockery::mock(Node::class . ', ' . PreprocessorInterface::class);

        $this->locator->shouldReceive('getPreprocessor')
                      ->once()
                      ->with('filter')
                      ->andReturn($preprocessor);

        $preprocessor->shouldReceive('process')
                     ->once()
                     ->with($input, [ 'field' => 'fieldA' ])
                     ->andReturn([ $input[0] ]);

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame([ $input[0] ], $results);
    }

    /**
     * Test that process() runs multiple preprocessors on the full dataset.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessRunsMultiplePreprocessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [
                [ 'filter' => [ 'field' => 'fieldA' ] ],
                [ 'sort' => [ 'field' => 'fieldC' ] ],
            ],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $afterFilter = [ $input[0] ];

        $filter = Mockery::mock(Node::class . ', ' . PreprocessorInterface::class);
        $sort   = Mockery::mock(Node::class . ', ' . PreprocessorInterface::class);

        $this->locator->shouldReceive('getPreprocessor')
                      ->once()
                      ->with('filter')
                      ->andReturn($filter);

        $filter->shouldReceive('process')
               ->once()
               ->with($input, [ 'field' => 'fieldA' ])
               ->andReturn($afterFilter);

        $this->locator->shouldReceive('getPreprocessor')
                      ->once()
                      ->with('sort')
                      ->andReturn($sort);

        $sort->shouldReceive('process')
             ->once()
             ->with($afterFilter, [ 'field' => 'fieldC' ])
             ->andReturn($afterFilter);

        $this->locator->shouldReceive('getProcessor')
                      ->never();

        $results = $this->class->process($input, $config);
        $this->assertSame($afterFilter, $results);
    }

    /**
     * Test that process() runs a single processor on each item.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessRunsSingleProcessor(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'processors' => [
                [ 'value' => [ 'hello' => 'world' ] ],
            ],
        ];

        $expected = [];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $processor = Mockery::mock(Node::class . ', ' . ProcessorInterface::class);

        $this->locator->shouldReceive('getProcessor')
                      ->once()
                      ->with('value')
                      ->andReturn($processor);

        foreach ($input as $item)
        {
            $processor->shouldReceive('process')
                      ->once()
                      ->with($item, [ 'hello' => 'world' ])
                      ->andReturnUsing(fn(array $data, array $stepConfig) => $data + $stepConfig);

            $expected[] = $item + [ 'hello' => 'world' ];
        }

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() runs multiple processors on each item.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessRunsMultipleProcessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'processors' => [
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
            ],
        ];

        $expected = [];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $this->locator->shouldReceive('getPreprocessor')
                      ->never();

        $processor = Mockery::mock(Node::class . ', ' . ProcessorInterface::class);

        $this->locator->shouldReceive('getProcessor')
                      ->with('value')
                      ->twice()
                      ->andReturn($processor);

        foreach ($input as $item)
        {
            $processor->shouldReceive('process')
                      ->with($item, [ 'hello' => 'world' ])
                      ->andReturnUsing(fn(array $data, array $stepConfig) => $data + $stepConfig);

            $item += [ 'hello' => 'world' ];

            $processor->shouldReceive('process')
                      ->with($item, [ 'foo' => 'bar' ])
                      ->andReturnUsing(fn(array $data, array $stepConfig) => $data + $stepConfig);

            $expected[] = $item + [ 'foo' => 'bar' ];
        }

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() runs both preprocessors and processors.
     *
     * @covers \Pipeline\Parsers\DefaultParser::process
     */
    public function testProcessRunsPreprocessorsAndProcessors(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Parsers/simple_data.json'), TRUE);
        $config = [
            'preprocessors' => [
                [ 'filter' => [ 'field' => 'fieldA' ] ],
            ],
            'processors' => [
                [ 'value' => [ 'hello' => 'world' ] ],
            ],
        ];

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Preprocessors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Preprocessors');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Run Processors');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Run Processors');

        $afterFilter = [ $input[0] ];

        $preprocessor = Mockery::mock(Node::class . ', ' . PreprocessorInterface::class);

        $this->locator->shouldReceive('getPreprocessor')
                      ->once()
                      ->with('filter')
                      ->andReturn($preprocessor);

        $preprocessor->shouldReceive('process')
                     ->once()
                     ->with($input, [ 'field' => 'fieldA' ])
                     ->andReturn($afterFilter);

        $processor = Mockery::mock(Node::class . ', ' . ProcessorInterface::class);

        $this->locator->shouldReceive('getProcessor')
                      ->once()
                      ->with('value')
                      ->andReturn($processor);

        $processor->shouldReceive('process')
                  ->once()
                  ->with($input[0], [ 'hello' => 'world' ])
                  ->andReturnUsing(fn(array $data, array $stepConfig) => $data + $stepConfig);

        $expected = [ $input[0] + [ 'hello' => 'world' ] ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

}

?>
