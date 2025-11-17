<?php

/**
 * This file contains the ArrayFlattenerProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Flatteners\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains tests for the ArrayFlattener class.
 *
 * @covers Pipeline\Flatteners\ArrayFlattener
 */
class ArrayFlattenerProcessTest extends ArrayFlattenerTestCase
{

    use PsrLoggerTestTrait;

    /**
     * Test that process() flattens multidimensional arrays.
     *
     * @covers Pipeline\Flatteners\ArrayFlattener::process
     */
    public function testProcess(): void
    {
        $data = [ [ 'item1' ], [ 'item2' ] ];

        $config = [];

        $result = $this->class->process($data, $config);

        $this->assertSame([ 'item1', 'item2' ], $result);
    }

    /**
     * Test that process() flattens multidimensional arrays.
     *
     * @covers Pipeline\Flatteners\ArrayFlattener::process
     */
    public function testProcessWithStringArray(): void
    {
        $data = [ '[{}]', '[{}]' ];

        $config = [];

        $result = $this->class->process($data, $config);

        $this->assertArrayEmpty($result);
    }

}

?>
