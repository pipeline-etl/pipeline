<?php

/**
 * This file contains the ProcessorPreprocessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Preprocessors\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Pipeline\Common\ProcessorRunnerInterface;
use Pipeline\Preprocessors\ProcessorPreprocessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ProcessorPreprocessor class.
 *
 * @covers Pipeline\Preprocessors\ProcessorPreprocessor
 */
abstract class ProcessorPreprocessorTestCase extends LunrBaseTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $logger;

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
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->runner = Mockery::mock(ProcessorRunnerInterface::class);

        $this->class = new ProcessorPreprocessor($this->logger);

        $this->baseSetUp($this->class);
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
