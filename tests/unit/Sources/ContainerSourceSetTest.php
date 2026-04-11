<?php

/**
 * This file contains the ContainerSourceSetTest class.
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
class ContainerSourceSetTest extends ContainerSourceTestCase
{

    /**
     * Test that set() replaces the data.
     *
     * @covers \Pipeline\Sources\ContainerSource::set
     */
    public function testSetReplacesData(): void
    {
        $this->class->set([[ 'hello' => 'world' ]]);
        $this->class->set([[ 'goodbye' => 'world' ]]);

        $results = $this->class->fetch([]);

        $this->assertSame([[ 'goodbye' => 'world' ]], $results);
    }

}

?>
