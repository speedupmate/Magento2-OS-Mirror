<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common\TaxAreaLookupResponseMapperTest;

use PHPUnit\Framework\TestCase;
use Vertex\Data\AddressInterface;
use Vertex\Data\JurisdictionInterface;
use Vertex\Mapper\TaxAreaLookupResponseMapperInterface;
use Vertex\Services\TaxAreaLookup\ResponseInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see TaxAreaLookupResponseMapper}
 */
class BuildTest extends TestCase
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
     * Test {@see TaxAreaLookupResponseMapper::build()}
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupResponseMapperInterface $mapper
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testBuild(TaxAreaLookupResponseMapperInterface $mapper)
    {
        $map = $this->buildMap();

        $object = $mapper->build($map);
        $results = $object->getResults();

        $this->assertCount(1, $results);
        $result = reset($results);

        $this->assertEquals('50371900', $result->getTaxAreaId());
        $this->assertEquals(100, $result->getConfidenceIndicator());

        $jurisdictions = $result->getJurisdictions();
        $this->assertCount(5, $jurisdictions);
        $this->assertCallableOnAnItem(
            function (JurisdictionInterface $jurisdiction) {
                return $jurisdiction->getName() === 'UNITED STATES'
                    && $jurisdiction->getLevel() === JurisdictionInterface::JURISDICTION_LEVEL_COUNTRY
                    && $jurisdiction->getEffectiveDate()
                    && $jurisdiction->getEffectiveDate()->format('Y-m-d') === '1900-01-01'
                    && $jurisdiction->getExpirationDate()
                    && $jurisdiction->getExpirationDate()->format('Y-m-d') === '9999-12-31'
                    && $jurisdiction->getId() === 1;
            },
            $jurisdictions,
            'Jurisdictions does not contain expected United States'
        );
        $this->assertCallableOnAnItem(
            function (JurisdictionInterface $jurisdiction) {
                return $jurisdiction->getName() === 'CALIFORNIA'
                    && $jurisdiction->getLevel() === JurisdictionInterface::JURISDICTION_LEVEL_STATE
                    && $jurisdiction->getEffectiveDate()
                    && $jurisdiction->getEffectiveDate()->format('Y-m-d') === '1900-01-01'
                    && $jurisdiction->getExpirationDate()
                    && $jurisdiction->getExpirationDate()->format('Y-m-d') === '9999-12-31'
                    && $jurisdiction->getId() === 2398;
            },
            $jurisdictions,
            'Jurisdictions does not contain expected California'
        );
        $this->assertCallableOnAnItem(
            function (JurisdictionInterface $jurisdiction) {
                return $jurisdiction->getName() === 'LOS ANGELES'
                    && $jurisdiction->getLevel() === JurisdictionInterface::JURISDICTION_LEVEL_COUNTY
                    && $jurisdiction->getEffectiveDate()
                    && $jurisdiction->getEffectiveDate()->format('Y-m-d') === '1900-01-01'
                    && $jurisdiction->getExpirationDate()
                    && $jurisdiction->getExpirationDate()->format('Y-m-d') === '9999-12-31'
                    && $jurisdiction->getId() === 2872;
            },
            $jurisdictions,
            'Jurisdictions does not contain expected Los Angeles County'
        );
        $this->assertCallableOnAnItem(
            function (JurisdictionInterface $jurisdiction) {
                return $jurisdiction->getName() === 'LOS ANGELES'
                    && $jurisdiction->getLevel() === JurisdictionInterface::JURISDICTION_LEVEL_CITY
                    && $jurisdiction->getEffectiveDate()
                    && $jurisdiction->getEffectiveDate()->format('Y-m-d') === '1900-01-01'
                    && $jurisdiction->getExpirationDate()
                    && $jurisdiction->getExpirationDate()->format('Y-m-d') === '9999-12-31'
                    && $jurisdiction->getId() === 2916;
            },
            $jurisdictions,
            'Jurisdictions does not contain expected Los Angeles city'
        );
        $this->assertCallableOnAnItem(
            function (JurisdictionInterface $jurisdiction) {
                return $jurisdiction->getName() === 'LOS ANGELES TOURISM MARKETING DISTRICT'
                    && $jurisdiction->getLevel() === JurisdictionInterface::JURISDICTION_LEVEL_DISTRICT
                    && $jurisdiction->getEffectiveDate()
                    && $jurisdiction->getEffectiveDate()->format('Y-m-d') === '1900-01-01'
                    && $jurisdiction->getExpirationDate()
                    && $jurisdiction->getExpirationDate()->format('Y-m-d') === '9999-12-31'
                    && $jurisdiction->getId() === 99051;
            },
            $jurisdictions,
            'Jurisdictions does not contain expected Los Angeles Tourism Marketing District'
        );

        /** @var AddressInterface[] $addresses */
        $addresses = $result->getPostalAddresses();
        $this->assertCount(1, $addresses);

        $address = reset($addresses);
        $this->assertEquals(['100 Universal City Plz'], $address->getStreetAddress());
        $this->assertEquals('Universal City', $address->getCity());
        $this->assertEquals('CA', $address->getMainDivision());
        $this->assertEquals('Los Angeles', $address->getSubDivision());
        $this->assertEquals('91608-1002', $address->getPostalCode());
        $this->assertEquals('USA', $address->getCountry());

        $statuses = $result->getStatuses();
        $this->assertCount(1, $statuses);

        $status = reset($statuses);
        $this->assertEquals('NORMAL', $status);
    }

    /**
     * Assert that an array contains an entry that matches a callable's expectation
     *
     * @param callable $callable
     * @param array $array
     * @param string $message
     * @return bool
     */
    private function assertCallableOnAnItem(callable $callable, array $array, $message = '')
    {
        foreach ($array as $item) {
            if ($callable($item)) {
                return true;
            }
        }

        $this->fail($message ?: 'array does not contain expectation');
        return false;
    }

    /**
     * Build the map for use with the tests
     *
     * @return \stdClass
     */
    private function buildMap()
    {
        $map = new \stdClass();
        $map->TaxAreaResponse = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->taxAreaId = '50371900';
        $map->TaxAreaResponse->TaxAreaResult->confidenceIndicator = 100;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction = [];
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->_ = 'UNITED STATES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->jurisdictionLevel = 'COUNTRY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->jurisdictionId = 1;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->_ = 'CALIFORNIA';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->jurisdictionLevel = 'STATE';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->jurisdictionId = 2398;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->_ = 'LOS ANGELES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->jurisdictionLevel = 'COUNTY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->jurisdictionId = 2872;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->_ = 'LOS ANGELES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->jurisdictionLevel = 'CITY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->jurisdictionId = 2916;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->_ = 'LOS ANGELES TOURISM MARKETING DISTRICT';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->jurisdictionLevel = 'DISTRICT';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->jurisdictionId = 99051;
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->StreetAddress1 = '100 Universal City Plz';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->City = 'Universal City';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->MainDivision = 'CA';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->SubDivision = 'Los Angeles';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->PostalCode = '91608-1002';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->Country = 'USA';
        $map->TaxAreaResponse->TaxAreaResult->Status = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Status->lookupResult = 'NORMAL';
        return $map;
    }
}
