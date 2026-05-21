<?php

/**
 * This file contains the ItemizeProcessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Processors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Processors\ItemizeProcessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ItemizeProcessor class.
 *
 * @covers Pipeline\Processors\ItemizeProcessor
 */
abstract class ItemizeProcessorTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Instance of the tested class.
     * @var ItemizeProcessor
     */
    protected ItemizeProcessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->class = new ItemizeProcessor($this->logger);
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
