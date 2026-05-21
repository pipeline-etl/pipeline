<?php

/**
 * This file contains the ForeachProcessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Common\ProcessorRunnerInterface;
use Pipeline\Processors\ForeachProcessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ForeachProcessor class.
 *
 * @covers Pipeline\Processors\ForeachProcessor
 */
abstract class ForeachProcessorTestCase extends MockeryTestCase
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
     * @var ForeachProcessor
     */
    protected ForeachProcessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->runner = Mockery::mock(ProcessorRunnerInterface::class);

        $this->class = new ForeachProcessor($this->logger);
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
