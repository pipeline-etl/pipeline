<?php

/**
 * This file contains the XmlFlattenerProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Flatteners;

/**
 * This class contains tests for the XmlFlattener class.
 *
 * @covers Pipeline\Flatteners\XmlFlattener
 */
class XmlFlattenerProcessTest extends XmlFlattenerTestCase
{

    /**
     * Test that process() ignores invalid XML.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessIgnoresInvalidXml(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');
        $xml     = 'String';

        $data = [
            $xml,
            $flights,
        ];

        $config = [
            'fields' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
    }

    /**
     * Test that process() returns empty if no fields are specified.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessReturnsEmptyIfNoFields(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'cows' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() selects data from the root if a root is provided.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessUsesProvidedRootElement(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');
        $data    = [ $flights ];

        $config = [
            'config' => [ 'root' => '/airport/flights' ],
            'fields' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
    }

    /**
     * Test that process() ignores source data where the root element could not be found.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessIgnoresSourceWithoutRootElement(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');
        $xml     = file_get_contents(TEST_STATICS . '/Flatteners/EmptyXmlExample.xml');

        $data = [
            $xml,
            $flights,
        ];

        $config = [
            'fields' => [ 'actualLandingTimestamp' => 'actualLandingTime' ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
    }

    /**
     * Test that process() works with simple key rules.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithSimpleKeyRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'actualLandingTimestamp'  => 'actualLandingTime',
                'actualOffBlockTimestamp' => 'actualOffBlockTime',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[0]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[0]);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[1]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[1]);

        $this->assertEquals('2024-01-15T08:30:00.000+01:00', $result[0]['actualLandingTimestamp']);
    }

    /**
     * Test that process() works with XPath rules.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithXPathRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'checkinStartTimestamp' => [ 'path' => './/checkinAllocations/checkinAllocations/element[1]/startTime' ],
                'checkinEndTimestamp'   => [ 'path' => './/checkinAllocations/checkinAllocations/element[1]/endTime' ],
                'prefixes'             => [ 'path' => './/prefixIATA|.//prefixICAO' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[0]);
        $this->assertArrayHasKey('checkinStartTimestamp', $result[1]);
        $this->assertArrayHasKey('checkinEndTimestamp', $result[1]);

        $this->assertEquals('2024-01-15T11:00:00.000+01:00', $result[4]['checkinStartTimestamp']);
        $this->assertEquals('2024-01-15T13:30:00.000+01:00', $result[4]['checkinEndTimestamp']);

        $this->assertEquals([ 'BB', 'BBE' ], $result[4]['prefixes']);
    }

    /**
     * Test that process() works with XPath rules and simplify disabled.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithXPathRulesSimplify(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'aircraftType' => [
                    'path'     => './/aircraftType',
                    'simplify' => FALSE,
                ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertIsArray($result[0]['aircraftType']);
        $this->assertIsObject($result[0]['aircraftType'][0]);
    }

    /**
     * Test that process() works with XPath rules referencing attributes.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithXPathRulesReferencingAttributes(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'checkinAllocations' => [ 'path' => './/checkinAllocations/@null' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('checkinAllocations', $result[0]);
        $this->assertArrayHasKey('checkinAllocations', $result[1]);

        $this->assertEquals('true', $result[0]['checkinAllocations']);
    }

    /**
     * Test that process() sets NULL for missing keys.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithMissingKeyRules(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'actualLandingTimestamp'  => 'foo',
                'actualOffBlockTimestamp' => 'foo',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[0]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[0]);
        $this->assertArrayHasKey('actualLandingTimestamp', $result[1]);
        $this->assertArrayHasKey('actualOffBlockTimestamp', $result[1]);
    }

    /**
     * Test that process() can get an item from the document root.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithItemFromDocumentRoot(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/SmallXmlExample.xml');

        $data = [ $xml ];

        $config = [
            'config' => [ 'root' => '//document/users' ],
            'fields' => [
                'email' => [ 'path' => '//user//email' ],
                'gate'  => [ 'path' => '/document/flights/element/gate' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEquals('john@example.com', $result[0]['email']);
        $this->assertEquals('C11', $result[0]['gate']);
    }

    /**
     * Test that process() uses a configured root element.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithRootConfig(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/SmallXmlExample.xml');

        $data = [ $xml ];

        $config = [
            'config' => [ 'root' => '//document/flights' ],
            'fields' => [
                'gate' => [ 'path' => '///element/gate' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(1, $result);
        $this->assertEquals('C11', $result[0]['gate']);
    }

    /**
     * Test that process() works with a configured root element and multiple sources.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithRootConfigAndMultipleSources(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/SmallXmlExample.xml');

        $data = [ $xml, $xml ];

        $config = [
            'config' => [ 'root' => '//document/flights' ],
            'fields' => [
                'gate' => [ 'path' => '///element/gate' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(2, $result);
        $this->assertEquals('C11', $result[0]['gate']);
    }

    /**
     * Test that process() ignores rogue empty elements.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessIgnoresRogueEmptyElements(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/RogueEmptyXmlExample.xml');

        $data = [ $xml ];

        $config = [
            'config' => [ 'root' => '//document/flights' ],
            'fields' => [
                'gate' => [ 'path' => '///element/gate' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(1, $result);
        $this->assertEquals('C11', $result[0]['gate']);
    }

    /**
     * Test that process() works correctly when using XML namespaces.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessWithXmlNamespace(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/namespaced.xml');

        $data = [ $xml ];

        $config = [
            'config' => [
                'root'      => '//ns0:FlightNotification',
                'namespace' => 'ns0',
            ],
            'fields' => [
                'flightNumber' => [
                    'path' => 'ns0:LegIdentifier/ns0:FlightNumber',
                ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(1, $result);
        $this->assertEquals('029', $result[0]['flightNumber']);
    }

    /**
     * Test that process() auto-detects nested root elements.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessAutoDetectsNestedRoot(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/NestedXmlExample.xml');

        $data = [ $xml ];

        $config = [
            'fields' => [
                'id' => 'id',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(2, $result);
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('2', $result[1]['id']);
    }

    /**
     * Test that process() returns NULL when the configured root xpath matches nothing.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessSkipsSourceWhenRootXpathMatchesNothing(): void
    {
        $xml = file_get_contents(TEST_STATICS . '/Flatteners/SingleItemXmlExample.xml');

        $data = [ $xml ];

        $config = [
            'config' => [ 'root' => '/root/nonexistent' ],
            'fields' => [
                'id' => 'id',
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertEmpty($result);
    }

    /**
     * Test that process() handles an invalid XPath expression in a field rule.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessHandlesInvalidXpathExpression(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'broken' => [ 'path' => '///invalid[[[' ],
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertFalse($result[0]['broken']);
    }

    /**
     * Test that process() sets NULL for field origins that are neither string nor array.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
     */
    public function testProcessSetsNullForIntegerFieldOrigin(): void
    {
        $flights = file_get_contents(TEST_STATICS . '/Flatteners/flights.xml');

        $data = [ $flights ];

        $config = [
            'fields' => [
                'index' => 0,
            ],
        ];

        $result = $this->class->process($data, $config);

        $this->assertCount(5, $result);
        $this->assertNull($result[0]['index']);
    }

    /**
     * Test that process() skips non-string source data.
     *
     * @covers Pipeline\Flatteners\XmlFlattener::process
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

}

?>
