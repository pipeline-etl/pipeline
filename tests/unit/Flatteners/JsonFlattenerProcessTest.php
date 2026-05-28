<?php

/**
 * This file contains the JsonFlattenerProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Flatteners;

use stdClass;

/**
 * This class contains tests for the JsonFlattener class.
 *
 * @covers Pipeline\Flatteners\JsonFlattener
 */
class JsonFlattenerProcessTest extends JsonFlattenerTestCase
{

    /**
     * Test that process() ignores invalid JSON.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessIgnoresInvalidJson(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');
        $json    = 'String';

        $data = [
            $json,
            $flights,
        ];

        $config = [
            'fields' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
    }

    /**
     * Test that process() ignores source data where the root element could not be found.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessIgnoresSourceWithoutFoundOrProvidedRootElement(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');
        $json    = json_encode(new stdClass());

        $data = [
            $json,
            $flights,
        ];

        $config = [
            'fields' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
    }

    /**
     * Test that process() returns empty if no fields are specified.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessEmptyWhenFieldsMissing(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');
        $json    = json_encode(new stdClass());

        $data = [
            $json,
            $flights,
        ];

        $config = [
            'cow' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() works with simple key rules.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithSimpleKeyRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'actualLandingTimestamp'  => 'actualLandingTime',
                'actualOffBlockTimestamp' => 'actualOffBlockTime',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[0]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[0]);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[1]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[1]);
    }

    /**
     * Test that process() ignores a present UTF-8 BOM.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessIgnoresUTF8BOM(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ chr(239) . chr(187) . chr(191) . $flights ];

        $config = [
            'fields' => [
                'actualLandingTimestamp'  => 'actualLandingTime',
                'actualOffBlockTimestamp' => 'actualOffBlockTime',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[0]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[0]);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[1]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[1]);
    }

    /**
     * Test that process() works with JSONPath rules.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithJsonPathRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'checkinStartTimestamp' => [ 'path' => 'checkinAllocations.checkinAllocations[0].startTime' ],
                'checkinEndTimestamp'   => [ 'path' => 'checkinAllocations.checkinAllocations[0].endTime' ],
                'schemaVersion'         => [ 'path' => '$.schemaVersion' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[1]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[1]);
        $this->assertArrayHasKey('schemaVersion', $result[0]);
        $this->assertArrayHasKey('schemaVersion', $result[1]);

        $this->assertNull($result[0]['checkinStartTimestamp']);
        $this->assertNull($result[0]['checkinEndTimestamp']);
        $this->assertEquals(3, $result[0]['schemaVersion']);
        $this->assertNull($result[1]['checkinStartTimestamp']);
        $this->assertNull($result[1]['checkinEndTimestamp']);
        $this->assertEquals(3, $result[1]['schemaVersion']);
    }

    /**
     * Test that process() works with JSON Pointer rules.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithJsonPointerRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'checkinStartTimestamp' => [ 'pointer' => '0/checkinAllocations/checkinAllocations/0/startTime' ],
                'checkinEndTimestamp'   => [ 'pointer' => '0/checkinAllocations/checkinAllocations/0/endTime' ],
                'schemaVersion'         => [ 'pointer' => '/schemaVersion' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[1]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[1]);
        $this->assertArrayHasKey('schemaVersion', $result[0]);
        $this->assertArrayHasKey('schemaVersion', $result[1]);

        $this->assertNull($result[0]['checkinStartTimestamp']);
        $this->assertNull($result[0]['checkinEndTimestamp']);
        $this->assertEquals(3, $result[0]['schemaVersion']);
        $this->assertNull($result[1]['checkinStartTimestamp']);
        $this->assertNull($result[1]['checkinEndTimestamp']);
        $this->assertEquals(3, $result[1]['schemaVersion']);
    }

    /**
     * Test that process() sets NULL for missing keys.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithMissingKeyRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'actualLandingTimestamp'  => 'foo',
                'actualOffBlockTimestamp' => 'foo',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[0]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[0]);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[1]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[1]);
    }

    /**
     * Test that process() works with a specified root element.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithSpecifiedRoot(): void
    {
        $entries = file_get_contents(TEST_STATICS . '/Flatteners/entries.json');

        $data = [ $entries ];

        $config = [
            'config' => [ 'root' => '$.includes.Entry' ],
            'fields' => [
                'id'   => 'id',
                'type' => 'type',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(6, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
    }

    /**
     * Test that process() works with a specified root element and multiple sources.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithSpecifiedRootAndMultipleSources(): void
    {
        $entries = file_get_contents(TEST_STATICS . '/Flatteners/entries.json');

        $data = [ $entries, $entries ];

        $config = [
            'config' => [ 'root' => '$.includes.Entry' ],
            'fields' => [
                'id'   => 'id',
                'type' => 'type',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(12, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
    }

    /**
     * Test that process() skips non-string source data.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessSkipsNonStringSourceData(): void
    {
        $data = [ [ 'not_a_string' ] ];

        $config = [
            'fields' => [ 'foo' => 'bar' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() skips sources that decode to a scalar value.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessSkipsScalarJsonSource(): void
    {
        $data = [ '42' ];

        $config = [
            'fields' => [ 'foo' => 'bar' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() skips non-object items in the root array.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessSkipsNonObjectItemsInRoot(): void
    {
        $data = [ '[1, 2, 3]' ];

        $config = [
            'fields' => [ 'foo' => 'bar' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() works with a root path without dollar prefix.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithRootPathWithoutDollarPrefix(): void
    {
        $entries = file_get_contents(TEST_STATICS . '/Flatteners/entries.json');

        $data = [ $entries ];

        $config = [
            'config' => [ 'root' => 'includes.Entry' ],
            'fields' => [
                'id'   => 'id',
                'type' => 'type',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(6, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
    }

    /**
     * Test that process() returns NULL for a JSONPath with an index on a non-array property.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithJsonPathIndexOnNonArray(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'code' => [ 'path' => 'airlineCode[1]' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertNull($result[0]['code']);
    }

    /**
     * Test that process() resolves absolute JSON Pointer hash fragments to key names.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithAbsoluteJsonPointerHashFragment(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'keyName' => [ 'pointer' => '/flights/0/airlineCode#' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertEquals('airlineCode', $result[0]['keyName']);
    }

    /**
     * Test that process() returns NULL for a JSON Pointer to a missing object property.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithJsonPointerToMissingProperty(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'missing' => [ 'pointer' => '/flights/0/nonexistent' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertNull($result[0]['missing']);
    }

    /**
     * Test that process() returns NULL for a JSON Pointer to a missing array key.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithJsonPointerToMissingArrayKey(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'missing' => [ 'pointer' => '/flights/999' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertNull($result[0]['missing']);
    }

    /**
     * Test that process() resolves relative JSON Pointers that navigate up levels.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithRelativeJsonPointer(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'version' => [ 'pointer' => '2/schemaVersion' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertEquals(3, $result[0]['version']);
    }

    /**
     * Test that process() auto-detects a nested root array.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessAutoDetectsNestedRootArray(): void
    {
        $json = json_encode((object) [
            'meta' => (object) [ 'version' => 1 ],
            'data' => (object) [
                'items' => [
                    (object) [ 'id' => 'a' ],
                    (object) [ 'id' => 'b' ],
                ],
            ],
        ]);

        $data = [ $json ];

        $config = [
            'fields' => [
                'id'      => 'id',
                'version' => [ 'pointer' => '3/meta/version' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(2, $result);
        $this->assertEquals('a', $result[0]['id']);
        $this->assertEquals('b', $result[1]['id']);
        $this->assertEquals(1, $result[0]['version']);
    }

    /**
     * Test that process() resolves relative JSON Pointer hash fragments.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithRelativeJsonPointerHashFragment(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'keyName' => [ 'pointer' => '1#' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertEquals('flights', $result[0]['keyName']);
    }

    /**
     * Test that process() sets NULL for a non-string path value.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithNonStringPathSetsNull(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'value' => [ 'path' => TRUE ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertNull($result[0]['value']);
    }

    /**
     * Test that process() sets NULL for a non-string pointer value.
     *
     * @covers Pipeline\Flatteners\JsonFlattener::process
     */
    public function testProcessWithNonStringPointerSetsNull(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.json');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'value' => [ 'pointer' => TRUE ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(20, $result);
        $this->assertNull($result[0]['value']);
    }

}

?>
