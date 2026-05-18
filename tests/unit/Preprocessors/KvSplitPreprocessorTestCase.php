<?php

/**
 * This file contains the KvSplitPreprocessorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Preprocessors;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Preprocessors\KvSplitPreprocessor;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the KvSplitPreprocessor class.
 *
 * @covers Pipeline\Preprocessors\KvSplitPreprocessor
 */
abstract class KvSplitPreprocessorTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Instance of the tested class.
     * @var KvSplitPreprocessor
     */
    protected KvSplitPreprocessor $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->class = new KvSplitPreprocessor($this->logger);
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
