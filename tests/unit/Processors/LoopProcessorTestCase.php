<?php

/**
 * This file contains the LoopProcessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Common\ProcessorRunnerInterface;
use Pipeline\Processors\LoopProcessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the LoopProcessor class.
 *
 * @covers Pipeline\Processors\LoopProcessor
 */
abstract class LoopProcessorTestCase extends MockeryTestCase
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
     * @var LoopProcessor
     */
    protected LoopProcessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->runner = Mockery::mock(ProcessorRunnerInterface::class);

        $this->class = new LoopProcessor($this->logger);
        $this->class->link($this->runner);
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
