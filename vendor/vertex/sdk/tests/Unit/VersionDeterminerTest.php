<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit;

use PHPUnit\Framework\TestCase;
use Vertex\Exception\ConfigurationException;
use Vertex\Utility\VersionDeterminer;

/**
 * Tests for {@see VersionDeterminer}
 */
class VersionDeterminerTest extends TestCase
{
    /** @var \Vertex\Utility\VersionDeterminer */
    private $versionDeterminer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->versionDeterminer = new VersionDeterminer();
    }

    /**
     * Provide data for primary test
     *
     * Expects to return two parameters, the first being the URL of an endpoint and the second being the expected API
     * level returned from {@see VersionDeterminer::execute()}
     *
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/CalculateTax60?wsdl', '60'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/CalculateTax60', '60'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas60?wsdl', '60'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas60', '60'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/CalculateTax70?wsdl', '70'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/CalculateTax70', '70'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas70?wsdl', '70'],
            ['https://mgcsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas70', '70'],
        ];
    }

    /**
     * Test that a {@see ConfigurationException} is thrown when no version is found for a URL
     *
     * @return void
     */
    public function testException()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Provided URL does not contain a known, supported version');
        $this->versionDeterminer->execute('https://www.google.com/');
    }

    /**
     * Test that the correct API level is returned for any given URL
     *
     * @dataProvider dataProvider
     * @param string $url
     * @param string $apiLevel
     * @return void
     * @throws \Vertex\Exception\ConfigurationException
     */
    public function testVersionDeterminer($url, $apiLevel)
    {
        $this->assertEquals($apiLevel, $this->versionDeterminer->execute($url));
    }
}
