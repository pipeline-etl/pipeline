<?php

/**
 * This file contains the PipelineSetProfilerTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Pipeline;

use Lunr\Ticks\Profiling\Profiler;
use Mockery;

/**
 * This class contains tests for the setProfiler() method of the Pipeline class.
 *
 * @covers \Pipeline\Pipeline
 */
class PipelineSetProfilerTest extends PipelineTestCase
{

    /**
     * Test that setProfiler() sets the profiler and adds a pipeline tag.
     *
     * @covers \Pipeline\Pipeline::setProfiler
     */
    public function testSetProfilerSetsProfilerAndAddsTag(): void
    {
        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_simple');

        $this->class->setProfiler($this->profiler);
    }

    /**
     * Test that setProfiler() does not overwrite a previously set profiler.
     *
     * @covers \Pipeline\Pipeline::setProfiler
     */
    public function testSetProfilerDoesNotOverwriteExistingProfiler(): void
    {
        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_simple');

        $this->class->setProfiler($this->profiler);

        $profiler2 = Mockery::mock(Profiler::class);

        $profiler2->shouldReceive('addTag')
                  ->never();

        $this->class->setProfiler($profiler2);
    }

}

?>
