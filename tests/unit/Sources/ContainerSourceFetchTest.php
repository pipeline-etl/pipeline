<?php

/**
 * This file contains the ContainerSourceFetchTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Sources;

/**
 * This class contains tests for the ContainerSource class.
 *
 * @covers \Pipeline\Sources\ContainerSource
 */
class ContainerSourceFetchTest extends ContainerSourceTestCase
{

    /**
     * Test that fetch() returns an empty array by default.
     *
     * @covers \Pipeline\Sources\ContainerSource::fetch
     */
    public function testFetchReturnsEmptyArrayByDefault(): void
    {
        $results = $this->class->fetch([]);

        $this->assertSame([], $results);
    }

    /**
     * Test that fetch() returns data that was set.
     *
     * @covers \Pipeline\Sources\ContainerSource::fetch
     */
    public function testFetchReturnsSetData(): void
    {
        $this->class->set([[ 'hello' => 'world' ]]);

        $results = $this->class->fetch([]);

        $this->assertSame([[ 'hello' => 'world' ]], $results);
    }

    /**
     * Test that fetch() returns data that was added.
     *
     * @covers \Pipeline\Sources\ContainerSource::fetch
     */
    public function testFetchReturnsAddedData(): void
    {
        $this->class->add([ 'hello' => 'world' ]);
        $this->class->add([ 'goodbye' => 'world' ]);

        $results = $this->class->fetch([]);

        $expected = [
            [ 'hello' => 'world' ],
            [ 'goodbye' => 'world' ],
        ];

        $this->assertSame($expected, $results);
    }

}

?>
