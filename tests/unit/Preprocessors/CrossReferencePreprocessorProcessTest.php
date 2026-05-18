<?php

/**
 * This file contains the CrossReferencePreprocessorProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Preprocessors;

/**
 * This class contains tests for the CrossReferencePreprocessor class.
 *
 * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor
 */
class CrossReferencePreprocessorProcessTest extends CrossReferencePreprocessorTestCase
{

    /**
     * Test process() with an empty config.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process
     */
    public function testProcessWithEmptyConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $config = [];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'CrossReferencePreprocessor',
                         'index' => '',
                         'message' => 'Incomplete configuration: No configuration defined!',
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config missing 'identifier'.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process
     */
    public function testProcessWithMissingIdentifierConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $config = [
            'field' => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'CrossReferencePreprocessor',
                         'index' => '',
                         'message' => "Incomplete configuration: 'identifier' not defined!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a non-array 'identifier' config.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process
     */
    public function testProcessWithNonArrayIdentifierConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $config = [
            'identifier' => 'invalid',
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'CrossReferencePreprocessor',
                         'index' => '',
                         'message' => "Invalid configuration: 'identifier' is not an array!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a config missing 'field'.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process
     */
    public function testProcessWithMissingFieldConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'CrossReferencePreprocessor',
                         'index' => '',
                         'message' => "Incomplete configuration: 'field' not defined!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test process() with a non-array 'field' config.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process
     */
    public function testProcessWithNonArrayFieldConfig(): void
    {
        $input  = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => 'invalid',
        ];

        $this->logger->shouldReceive('log')
                     ->once()
                     ->with('warning', '[{class}]{index} {message}', [
                         'class' => 'CrossReferencePreprocessor',
                         'index' => '',
                         'message' => "Invalid configuration: 'field' is not an array!",
                     ]);

        $results = $this->class->process($input, $config);
        $this->assertSame($input, $results);
    }

    /**
     * Test that process() works correctly with a single match.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithSingleMatch(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_single_match_result.json'), TRUE);

        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() works correctly with multiple matches.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithMultipleMatches(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_multiple_matches.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_multiple_matches_result.json'), TRUE);

        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() works correctly with mixed data.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithMixedData(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_mixed.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_mixed_result.json'), TRUE);

        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() works correctly with no matches.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithNoMatches(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_no_matches.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_no_matches_result.json'), TRUE);

        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() works correctly with multiple identifier keys.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithMultipleKeys(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_multiple_keys.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_multiple_keys_result.json'), TRUE);

        $config = [
            'identifier' => [
                'sku'  => 'parentSku',
                'date' => 'date',
            ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

    /**
     * Test that process() does not overwrite existing destination keys when there's no match.
     *
     * @covers \Pipeline\Preprocessors\CrossReferencePreprocessor::process

     */
    public function testProcessWithNoMatchesAndAlreadyExistingDestinationKey(): void
    {
        $input    = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_no_matches.json'), TRUE);
        $expected = json_decode(file_get_contents(TEST_STATICS . '/Preprocessors/crossref_no_matches_result.json'), TRUE);

        $config = [
            'identifier' => [ 'sku' => 'parentSku' ],
            'field'      => [
                'parentBrand'    => 'brand',
                'parentCategory' => 'category',
                'brand'          => 'brand',
            ],
        ];

        $results = $this->class->process($input, $config);
        $this->assertSame($expected, $results);
    }

}

?>
