<?php

/**
 * This file contains the DefaultParserTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Parsers;

use Lunr\Ticks\Profiling\Profiler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Pipeline\Common\Locator;
use Pipeline\Parsers\DefaultParser;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the DefaultParser class.
 *
 * @covers Pipeline\Parsers\DefaultParser
 */
abstract class DefaultParserTestCase extends MockeryTestCase
{

    /**
     * Mock instance of a logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of a profiler class.
     * @var Profiler&MockInterface
     */
    protected Profiler&MockInterface $profiler;

    /**
     * Mock instance of the Locator class.
     * @var Locator&MockInterface
     */
    protected Locator&MockInterface $locator;

    /**
     * Instance of the tested class.
     * @var DefaultParser
     */
    protected DefaultParser $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger   = Mockery::mock(LoggerInterface::class);
        $this->profiler = Mockery::mock(Profiler::class);
        $this->locator  = Mockery::mock(Locator::class);

        $this->class = new DefaultParser($this->logger, $this->profiler, $this->locator);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->locator);
        unset($this->profiler);
        unset($this->logger);

        parent::tearDown();
    }

}

?>
