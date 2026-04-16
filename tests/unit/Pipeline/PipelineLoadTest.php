<?php

/**
 * This file contains the PipelineLoadTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Pipeline;

use Mockery;
use Pipeline\Common\Exceptions\InvalidConfigurationException;
use Pipeline\Common\InfoInterface;
use Pipeline\Pipeline;

/**
 * This class contains tests for the load() method of the Pipeline class.
 *
 * @covers \Pipeline\Pipeline
 */
class PipelineLoadTest extends PipelineTestCase
{

    /**
     * Test that load() throws an exception for invalid JSON.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadThrowsExceptionForInvalidJson(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_invalid.json');

        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_invalid');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Failed parsing pipeline description!');

        $this->class->load(NULL);
    }

    /**
     * Test that load() throws an exception when JSON is not an object.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadThrowsExceptionForNonObjectJson(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_scalar.json');

        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_scalar');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Pipeline description has invalid format!');

        $this->class->load(NULL);
    }

    /**
     * Test that load() throws an exception when sources are missing.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadThrowsExceptionForMissingSources(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_empty.json');

        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_empty');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Failed loading pipeline description! Missing pipeline sources');

        $this->class->load(NULL);
    }

    /**
     * Test that load() throws an exception when flattener is missing.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadThrowsExceptionForMissingFlattener(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_sources.json');

        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_sources');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Failed loading pipeline description! Missing pipeline flattener');

        $this->class->load(NULL);
    }

    /**
     * Test that load() returns TRUE on success.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadReturnsTrueOnSuccess(): void
    {
        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_simple');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->assertTrue($this->class->load(NULL));
    }

    /**
     * Test that load() sets the profiler from the info object.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadSetsProfilerFromInfo(): void
    {
        $info = Mockery::mock(InfoInterface::class);

        $info->shouldReceive('setPipelineIdentifier')
             ->once()
             ->with('pipeline_simple');

        $info->shouldReceive('getProfiler')
             ->once()
             ->andReturn($this->profiler);

        $info->shouldReceive('getInfoIdentifier')
             ->once()
             ->andReturn('info');

        $info->shouldReceive('populate')
             ->once()
             ->with([]);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->assertTrue($this->class->load($info));
    }

    /**
     * Test that load() populates the info object with info data.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadPopulatesInfoObject(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_info.json');

        $info = Mockery::mock(InfoInterface::class);

        $info->shouldReceive('setPipelineIdentifier')
             ->once()
             ->with('pipeline_info');

        $info->shouldReceive('getProfiler')
             ->once()
             ->andReturn($this->profiler);

        $info->shouldReceive('getInfoIdentifier')
             ->once()
             ->andReturn('info');

        $info->shouldReceive('populate')
             ->once()
             ->with([]);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->assertTrue($this->class->load($info));
    }

    /**
     * Test that load() populates the info object with import_info data.
     *
     * @covers \Pipeline\Pipeline::load
     */
    public function testLoadPopulatesInfoObjectWithImportInfo(): void
    {
        $this->class = new Pipeline($this->locator, TEST_STATICS . '/Pipeline/pipeline_import_info.json');

        $expectedInfo = [
            'table'         => 'foo',
            'content-type'  => 'bar',
            'content-range' => [
                [ 'identifier' => [] ],
            ],
            'flags' => [
                'skip_empty' => FALSE,
            ],
            'hooks' => [ 'hook1', 'hook2' ],
            'notifications' => [
                [
                    'queue'     => 'Notifications1',
                    'job'       => [ 'new' => 'NewDataJob1', 'obsolete' => 'ObsoleteDataJob1' ],
                    'languages' => [ 'en-US' ],
                ],
                [
                    'queue'     => 'Notifications2',
                    'job'       => [ 'new' => 'NewDataJob2', 'obsolete' => 'ObsoleteDataJob2' ],
                    'languages' => [ 'en-US' ],
                ],
            ],
        ];

        $info = Mockery::mock(InfoInterface::class);

        $info->shouldReceive('setPipelineIdentifier')
             ->once()
             ->with('pipeline_import_info');

        $info->shouldReceive('getProfiler')
             ->once()
             ->andReturn($this->profiler);

        $info->shouldReceive('getInfoIdentifier')
             ->once()
             ->andReturn('import_info');

        $info->shouldReceive('populate')
             ->once()
             ->with($expectedInfo);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Load JSON definition');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Load JSON definition');

        $this->assertTrue($this->class->load($info));
    }

}

?>
