<?php

/**
 * This file contains the ContainerSourceAddTest class.
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
class ContainerSourceAddTest extends ContainerSourceTestCase
{

    /**
     * Test that add() appends data.
     *
     * @covers \Pipeline\Sources\ContainerSource::add
     */
    public function testAddAppendsData(): void
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
