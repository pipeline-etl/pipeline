<?php

/**
 * This file contains the ArrayFlattenerTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Flatteners\Tests;

use Lunr\Halo\LunrBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Pipeline\Flatteners\ArrayFlattener;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the ArrayFlattener class.
 *
 * @covers Pipeline\Flatteners\ArrayFlattener
 */
abstract class ArrayFlattenerTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $logger;

    /**
     * Instance of the tested class.
     * @var ArrayFlattener
     */
    protected ArrayFlattener $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $this->class = new ArrayFlattener($this->logger);

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
