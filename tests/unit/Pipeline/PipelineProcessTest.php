<?php

/**
 * This file contains the PipelineProcessTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Pipeline;

use Pipeline\Common\Exceptions\InvalidConfigurationException;

/**
 * This class contains tests for the process() method of the Pipeline class.
 *
 * @covers \Pipeline\Pipeline
 */
class PipelineProcessTest extends PipelineTestCase
{

    /**
     * Set up the profiler expectations for process().
     *
     * @return void
     */
    private function expectProcessSteps(): void
    {
        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Process sources');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Flattening data');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Configuring Parser');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Process sources');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Flattening data');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Configuring Parser');
    }

    /**
     * Test that process() returns flattened data from a simple pipeline.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessReturnsDataFromSimplePipeline(): void
    {
        $this->loadPipeline('pipeline_simple.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1', 'key2' => 'value2' ]];
        $flattenedData = [[ 'nkey1' => 'value1', 'nkey2' => 'value2' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() fetches source with empty config when source has no configuration.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessFetchesSourceWithEmptyConfig(): void
    {
        $this->loadPipeline('pipeline_empty_source_config.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() returns parsed data when a parser is configured.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessReturnsDataFromPipelineWithParser(): void
    {
        $this->loadPipeline('pipeline_with_parser.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1', 'key2' => 'value2' ]];
        $flattenedData = [[ 'nkey1' => 'value1', 'nkey2' => 'value2' ]];
        $parsedData    = [[ 'nkey1' => TRUE, 'nkey2' => 'value2' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);
        $parser    = $this->getParserMock($parsedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->locator->shouldReceive('getParser')
                      ->once()
                      ->with('foo')
                      ->andReturn($parser);

        $result = $this->class->process(NULL);

        $this->assertSame($parsedData, $result);
    }

    /**
     * Test that process() wraps preprocessors into a parser config.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessWrapsPreprocessorsIntoParser(): void
    {
        $this->loadPipeline('pipeline_with_preprocessors.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1', 'key2' => 'value2' ]];
        $flattenedData = [[ 'nkey1' => 'value1', 'nkey2' => 'value2' ]];
        $parsedData    = [[ 'nkey1' => 'processed', 'nkey2' => 'value2' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);
        $parser    = $this->getParserMock($parsedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->locator->shouldReceive('getParser')
                      ->once()
                      ->with('default')
                      ->andReturn($parser);

        $result = $this->class->process(NULL);

        $this->assertSame($parsedData, $result);
    }

    /**
     * Test that process() uses the specified environment config.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessUsesSpecifiedEnvironment(): void
    {
        $this->loadPipeline('pipeline_with_environments.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process('acceptance');

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() falls back to production when an unknown environment is specified.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessFallsBackToProductionForUnknownEnvironment(): void
    {
        $this->loadPipeline('pipeline_with_environments.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->logger->shouldReceive('warning')
                     ->once()
                     ->with('Environment "staging" is unknown! Proceeding with "production"');

        $result = $this->class->process('staging');

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() defaults to production when no environment is specified.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessDefaultsToProductionWhenNoEnvironmentSpecified(): void
    {
        $this->loadPipeline('pipeline_with_environments.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() skips source when no production environment is set and no environment specified.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessSkipsSourceWhenNoProductionAndNoEnvironmentSpecified(): void
    {
        $this->loadPipeline('pipeline_without_production_env.json');
        $this->expectProcessSteps();

        $flattenedData = [];

        $source    = $this->getSourceMock();
        $flattener = $this->getFlattenerMock($flattenedData);

        $source->shouldReceive('fetch')
               ->never();

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->logger->shouldReceive('warning')
                     ->once()
                     ->with('Production environment not set and no environment option specified! Skipping!');

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() skips source when unknown environment is specified and no production exists.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessSkipsSourceWhenUnknownEnvironmentAndNoProduction(): void
    {
        $this->loadPipeline('pipeline_without_production_env.json');
        $this->expectProcessSteps();

        $flattenedData = [];

        $source    = $this->getSourceMock();
        $flattener = $this->getFlattenerMock($flattenedData);

        $source->shouldReceive('fetch')
               ->never();

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->logger->shouldReceive('warning')
                     ->once()
                     ->with('Environment "staging" is unknown! No valid environment found! Skipping!');

        $result = $this->class->process('staging');

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() merges data from multiple sources.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessMergesDataFromMultipleSources(): void
    {
        $this->loadPipeline('pipeline_multiple_sources.json');
        $this->expectProcessSteps();

        $sourceData1   = [[ 'key1' => 'value1' ]];
        $sourceData2   = [[ 'key1' => 'value2' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ], [ 'nkey1' => 'value2' ]];

        $source1   = $this->getSourceMock($sourceData1);
        $source2   = $this->getSourceMock($sourceData2);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->twice()
                      ->with('foo')
                      ->andReturn($source1, $source2);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() skips a source when the locator returns NULL.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessSkipsSourceWhenLocatorReturnsNull(): void
    {
        $this->loadPipeline('pipeline_simple.json');
        $this->expectProcessSteps();

        $flattenedData = [];

        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn(NULL);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $result = $this->class->process(NULL);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() throws an exception when flattener is not found.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessThrowsExceptionWhenFlattenerNotFound(): void
    {
        $this->loadPipeline('pipeline_simple.json');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Process sources');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Flattening data');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Process sources');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Flattening data');

        $sourceData = [[ 'key1' => 'value1' ]];

        $source = $this->getSourceMock($sourceData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn(NULL);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Flattener "foo" not found!');

        $this->class->process(NULL);
    }

    /**
     * Test that process() throws an exception when parser is not found.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessThrowsExceptionWhenParserNotFound(): void
    {
        $this->loadPipeline('pipeline_with_parser.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->locator->shouldReceive('getParser')
                      ->once()
                      ->with('foo')
                      ->andReturn(NULL);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Parser "foo" not found!');

        $this->class->process(NULL);
    }

    /**
     * Test that process() uses mocked source data when a mock path is given.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessUsesMockedSourceData(): void
    {
        $this->loadPipeline('pipeline_simple.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $mockFile = tempnam(sys_get_temp_dir(), 'pipeline_test_');
        file_put_contents($mockFile, serialize($sourceData));

        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->never();

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->logger->shouldReceive('info')
                     ->once()
                     ->with('Using recorded source data from ' . $mockFile);

        $result = $this->class->process(NULL, FALSE, $mockFile);

        unlink($mockFile);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() records source data when record is TRUE.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessRecordsSourceData(): void
    {
        $this->loadPipeline('pipeline_simple.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1' ]];
        $flattenedData = [[ 'nkey1' => 'value1' ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $expectedFile = sys_get_temp_dir() . '/pipeline_simple.data';

        $this->logger->shouldReceive('info')
                     ->once()
                     ->with('Recorded source data at ' . $expectedFile);

        $result = $this->class->process(NULL, TRUE);

        $this->assertFileExists($expectedFile);
        $this->assertSame($sourceData, unserialize(file_get_contents($expectedFile)));

        unlink($expectedFile);

        $this->assertSame($flattenedData, $result);
    }

    /**
     * Test that process() throws an exception when no flattener is configured.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessThrowsExceptionWhenNoFlattenerConfigured(): void
    {
        $this->profiler->shouldReceive('addTag')
                       ->once()
                       ->with('pipeline', 'pipeline_simple');

        $this->class->setProfiler($this->profiler);

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Process sources');

        $this->profiler->shouldReceive('startNewSpan')
                       ->once()
                       ->with('Flattening data');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Process sources');

        $this->logger->shouldReceive('notice')
                     ->once()
                     ->with('Flattening data');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('No flattener configured!');

        $this->class->process(NULL);
    }

    /**
     * Test that process() strips non-scalar values when no parser is configured.
     *
     * @covers \Pipeline\Pipeline::process
     */
    public function testProcessStripsNonScalarValuesWithoutParser(): void
    {
        $this->loadPipeline('pipeline_simple.json');
        $this->expectProcessSteps();

        $sourceData    = [[ 'key1' => 'value1', 'key2' => 'value2' ]];
        $flattenedData = [[ 'nkey1' => 'value1', 'nkey2' => [ 'nested' => 'data' ] ]];

        $source    = $this->getSourceMock($sourceData);
        $flattener = $this->getFlattenerMock($flattenedData);

        $this->locator->shouldReceive('getSource')
                      ->once()
                      ->with('foo')
                      ->andReturn($source);

        $this->locator->shouldReceive('getFlattener')
                      ->once()
                      ->with('foo')
                      ->andReturn($flattener);

        $this->logger->shouldReceive('warning')
                     ->once()
                     ->with('Stripped non-scalar values from pipeline output without parser');

        $result = $this->class->process(NULL);

        $expected = [[ 'nkey1' => 'value1', 'nkey2' => NULL ]];

        $this->assertSame($expected, $result);
    }

}

?>
