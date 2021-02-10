<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common\TaxAreaLookupResponseMapperTest;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\Jurisdiction;
use Vertex\Data\JurisdictionInterface;
use Vertex\Data\TaxAreaLookupResult;
use Vertex\Data\TaxAreaLookupResultInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\TaxAreaLookupRequestMapper;
use Vertex\Mapper\TaxAreaLookupResponseMapperInterface;
use Vertex\Services\TaxAreaLookup\Response;
use Vertex\Services\TaxAreaLookup\ResponseInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see TaxAreaLookupResponseMapper}
 */
class MapTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(ResponseInterface::class);
    }

    /**
     * Test {@see TaxAreaLookupResponseMapper::map()}
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupResponseMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(TaxAreaLookupResponseMapperInterface $mapper)
    {
        $result1AreaId = CommonMapperProvider::randBasedOnMethodAvaialbility(
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MIN,
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MAX
        );
        $result2AreaId = CommonMapperProvider::randBasedOnMethodAvaialbility(
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MIN,
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MAX
        );

        $response = $this->buildResponse($result1AreaId, $result2AreaId);

        $map = $mapper->map($response)->TaxAreaResponse;

        $this->assertIsArray($map->TaxAreaResult);
        $this->assertCount(2, $map->TaxAreaResult);

        $firstResult = reset($map->TaxAreaResult);
        $secondResult = next($map->TaxAreaResult);

        $this->assertEquals($result1AreaId, $firstResult->taxAreaId);
        $this->assertEquals($result2AreaId, $secondResult->taxAreaId);

        $this->assertEquals(50, $firstResult->confidenceIndicator);
        $this->assertEquals(29, $secondResult->confidenceIndicator);

        $this->assertEquals('NORMAL', $firstResult->Status->lookupResult);
        $this->assertEquals('NORMAL', $secondResult->Status->lookupResult);

        $firstResultAddress = $firstResult->PostalAddress;
        $this->assertEquals('1000 Universal St', $firstResultAddress->StreetAddress1);
        $this->assertNotTrue(isset($firstResultAddress->StreetAddress2));
        $this->assertEquals('Universal City', $firstResultAddress->City);
        $this->assertEquals('CA', $firstResultAddress->MainDivision);
        $this->assertEquals('USA', $firstResultAddress->Country);

        $firstResultFirstJurisdiction = reset($firstResult->Jurisdiction);
        $firstResultSecondJurisdiction = next($firstResult->Jurisdiction);
        $this->assertEquals('California', $firstResultFirstJurisdiction->_);
        $this->assertEquals('STATE', $firstResultFirstJurisdiction->jurisdictionLevel);
        $this->assertEquals('1900-01-01', $firstResultFirstJurisdiction->effectiveDate);
        $this->assertEquals('9999-12-31', $firstResultFirstJurisdiction->expirationDate);
        $this->assertEquals(5, $firstResultFirstJurisdiction->jurisdictionId);

        $this->assertEquals('Universal City', $firstResultSecondJurisdiction->_);
        $this->assertEquals('CITY', $firstResultSecondJurisdiction->jurisdictionLevel);
        $this->assertEquals('1900-01-01', $firstResultSecondJurisdiction->effectiveDate);
        $this->assertEquals('9999-12-31', $firstResultSecondJurisdiction->expirationDate);
        $this->assertEquals(22, $firstResultSecondJurisdiction->jurisdictionId);

        $secondResultAddress = $secondResult->PostalAddress;
        $this->assertEquals('2001 Universal City', $secondResultAddress->StreetAddress1);
        $this->assertNotTrue(isset($secondResultAddress->StreetAddress2));
        $this->assertEquals('Universal City', $secondResultAddress->City);
        $this->assertEquals('CA', $secondResultAddress->MainDivision);
        $this->assertEquals('USA', $secondResultAddress->Country);
    }

    /**
     * Build the Response object for use with tests
     *
     * @param string $result1AreaId
     * @param string $result2AreaId
     * @return Response
     */
    private function buildResponse($result1AreaId, $result2AreaId)
    {
        $result1 = new TaxAreaLookupResult();
        $result1->setTaxAreaId($result1AreaId);
        $result1->setConfidenceIndicator(50);
        $result1->setStatuses([TaxAreaLookupResultInterface::STATUS_NORMAL]);

        $address1 = new Address();
        $address1->setStreetAddress(['1000 Universal St']);
        $address1->setCity('Universal City');
        $address1->setMainDivision('CA');
        $address1->setCountry('USA');

        $stateJurisdiction = new Jurisdiction();
        $stateJurisdiction->setName('California');
        $stateJurisdiction->setLevel(JurisdictionInterface::JURISDICTION_LEVEL_STATE);
        $stateJurisdiction->setEffectiveDate(new \DateTime('1900-01-01'));
        $stateJurisdiction->setExpirationDate(new \DateTime('9999-12-31'));
        $stateJurisdiction->setId(5);

        $cityJurisdiction = new Jurisdiction();
        $cityJurisdiction->setName('Universal City');
        $cityJurisdiction->setLevel(JurisdictionInterface::JURISDICTION_LEVEL_CITY);
        $cityJurisdiction->setEffectiveDate(new \DateTime('1900-01-01'));
        $cityJurisdiction->setExpirationDate(new \DateTime('9999-12-31'));
        $cityJurisdiction->setId(22);

        $result1->setPostalAddresses([$address1]);
        $result1->setJurisdictions([$stateJurisdiction, $cityJurisdiction]);

        $address2 = new Address();
        $address2->setStreetAddress(['2001 Universal City']);
        $address2->setCity('Universal City');
        $address2->setMainDivision('CA');
        $address2->setCountry('USA');

        $result2 = new TaxAreaLookupResult();
        $result2->setTaxAreaId($result2AreaId);
        $result2->setConfidenceIndicator(29);
        $result2->setStatuses([TaxAreaLookupResultInterface::STATUS_NORMAL]);
        $result2->setPostalAddresses([$address2]);
        $result2->setJurisdictions([$stateJurisdiction]);

        $response = new Response();
        $response->setResults([$result1, $result2]);
        return $response;
    }
}
