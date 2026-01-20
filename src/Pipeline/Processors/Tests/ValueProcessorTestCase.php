<?php

/**
 * This file contains the ValueProcessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Processors\Tests;

use Lunr\Halo\LunrBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Pipeline\Processors\ValueProcessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ValueProcessor class.
 *
 * @covers Pipeline\Processors\ValueProcessor
 */
abstract class ValueProcessorTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $logger;

    /**
     * Instance of the tested class.
     * @var ValueProcessor
     */
    protected ValueProcessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $this->class = new ValueProcessor($this->logger);

        $this->baseSetUp($this->class);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);

        parent::tearDown();
    }

}

?>
