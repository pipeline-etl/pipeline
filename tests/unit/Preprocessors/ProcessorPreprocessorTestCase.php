<?php

/**
 * This file contains the ProcessorPreprocessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Preprocessors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Common\ProcessorRunnerInterface;
use Pipeline\Preprocessors\ProcessorPreprocessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ProcessorPreprocessor class.
 *
 * @covers Pipeline\Preprocessors\ProcessorPreprocessor
 */
abstract class ProcessorPreprocessorTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of a processor runner.
     * @var ProcessorRunnerInterface&MockInterface
     */
    protected ProcessorRunnerInterface&MockInterface $runner;

    /**
     * Instance of the tested class.
     * @var ProcessorPreprocessor
     */
    protected ProcessorPreprocessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->runner = Mockery::mock(ProcessorRunnerInterface::class);

        $this->class = new ProcessorPreprocessor($this->logger);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->runner);
        unset($this->logger);

        parent::tearDown();
    }

}

?>
