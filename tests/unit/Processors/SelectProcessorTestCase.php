<?php

/**
 * This file contains the SelectProcessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Processors\SelectProcessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the SelectProcessor class.
 *
 * @covers Pipeline\Processors\SelectProcessor
 */
abstract class SelectProcessorTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Instance of the tested class.
     * @var SelectProcessor
     */
    protected SelectProcessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->class = new SelectProcessor($this->logger);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->logger);

        parent::tearDown();
    }

}

?>
