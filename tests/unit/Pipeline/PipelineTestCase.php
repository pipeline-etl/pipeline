<?php

/**
 * This file contains the PipelineTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Pipeline;

use Lunr\Ticks\Profiling\Profiler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Common\FlattenerInterface;
use Pipeline\Common\Locator;
use Pipeline\Common\Node;
use Pipeline\Common\Parser;
use Pipeline\Common\ProcessorInterface;
use Pipeline\Common\SourceInterface;
use Pipeline\Pipeline;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Pipeline class.
 *
 * @covers \Pipeline\Pipeline
 */
abstract class PipelineTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of the Locator class.
     * @var Locator&MockInterface
     */
    protected Locator&MockInterface $locator;

    /**
     * Mock instance of a profiler class.
     * @var Profiler&MockInterface
     */
    protected Profiler&MockInterface $profiler;

    /**
     * Instance of the tested class.
     * @var Pipeline
     */
    protected Pipeline $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger   = Mockery::mock(LoggerInterface::class);
        $this->locator  = Mockery::mock(Locator::class);
        $this->profiler = Mockery::mock(Profiler::class);

        $this->locator->shouldReceive('getLogger')
                      ->atLeast()
                      ->once()
                      ->andReturn($this->logger);

        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_simple.json');
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->profiler);
        unset($this->locator);
        unset($this->logger);

        parent::tearDown();
    }

    /**
     * Load a pipeline from a fixture file.
     *
     * @param string $fixture Fixture filename (without directory)
     *
     * @return void
     */
    protected function loadPipeline(string $fixture): void
    {
        $name = basename($fixture, '.json');

        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/' . $fixture);

        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', $name);

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->class->load(NULL);
    }

    /**
     * Create a mock source that returns the given data.
     *
     * @param array $data Data to return from fetch()
     *
     * @return SourceInterface&Node&MockInterface
     */
    protected function getSourceMock(array $data = []): SourceInterface&Node&MockInterface
    {
        $source = Mockery::mock(SourceInterface::class, Node::class);

        if ($data !== [])
        {
            $source->shouldReceive('fetch')
                   ->once()
                   ->andReturn($data);
        }

        return $source;
    }

    /**
     * Create a mock flattener that returns the given data.
     *
     * @param array $data Data to return from process()
     *
     * @return FlattenerInterface&Node&MockInterface
     */
    protected function getFlattenerMock(array $data = []): FlattenerInterface&Node&MockInterface
    {
        $flattener = Mockery::mock(FlattenerInterface::class, Node::class);

        $flattener->shouldReceive('process')
                  ->once()
                  ->andReturn($data);

        return $flattener;
    }

    /**
     * Create a mock parser that returns the given data.
     *
     * @param array $data Data to return from process()
     *
     * @return Parser&MockInterface
     */
    protected function getParserMock(array $data = []): Parser&MockInterface
    {
        $parser = Mockery::mock(Parser::class);

        $parser->shouldReceive('process')
               ->once()
               ->andReturn($data);

        return $parser;
    }

    /**
     * Create a mock processor that returns the given data.
     *
     * @param array $data Data to return from process()
     *
     * @return ProcessorInterface&Node&MockInterface
     */
    protected function getProcessorMock(array $data = []): ProcessorInterface&Node&MockInterface
    {
        $processor = Mockery::mock(ProcessorInterface::class, Node::class);

        $processor->shouldReceive('process')
                  ->once()
                  ->andReturn($data);

        return $processor;
    }

}

?>
