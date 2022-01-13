<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\TaxAreaLookupRequestMapper;
use Vertex\Mapper\TaxAreaLookupRequestMapperInterface;
use Vertex\Services\TaxAreaLookup\Request;
use Vertex\Services\TaxAreaLookup\RequestInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see TaxAreaLookupRequestMapper}
 *
 * @covers \Vertex\Mapper\Api60\TaxAreaLookupRequestMapper
 */
class TaxAreaLookupRequestMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(RequestInterface::class);
    }

    /**
     * Test {@see TaxAreaLookupRequestMapper::build()} with just a Postal Address
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupRequestMapperInterface $mapper
     * @return void
     */
    public function testBuildWithAddress(TaxAreaLookupRequestMapperInterface $mapper)
    {
        $map = new \stdClass();
        $map->TaxAreaRequest = new \stdClass();
        $map->TaxAreaRequest->TaxAreaLookup = new \stdClass();
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress = new \stdClass();
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->StreetAddress1 = 'Line 1';
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->StreetAddress2 = 'Line 2';
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->PostalCode = 99999;
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->City = 'Universal City';
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->MainDivision = 'CA';
        $map->TaxAreaRequest->TaxAreaLookup->PostalAddress->Country = 'USA';

        $object = $mapper->build($map);
        $address = $object->getPostalAddress();
        $this->assertEquals(['Line 1', 'Line 2'], $address->getStreetAddress());
        $this->assertEquals(99999, $address->getPostalCode());
        $this->assertEquals('Universal City', $address->getCity());
        $this->assertEquals('CA', $address->getMainDivision());
        $this->assertEquals('USA', $address->getCountry());
    }

    /**
     * Test {@see TaxAreaLookupRequestMapper::build()} with just a TaxAreaId
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupRequestMapperInterface $mapper
     * @return void
     */
    public function testBuildWithTaxAreaId(TaxAreaLookupRequestMapperInterface $mapper)
    {
        $taxAreaId = CommonMapperProvider::randBasedOnMethodAvaialbility(
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MIN,
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MAX
        );
        $map = new \stdClass();
        $map->TaxAreaRequest = new \stdClass();
        $map->TaxAreaRequest->TaxAreaLookup = new \stdClass();
        $map->TaxAreaRequest->TaxAreaLookup->TaxAreaId = $taxAreaId;

        $object = $mapper->build($map);

        $this->assertEquals($taxAreaId, $object->getTaxAreaId());
    }

    /**
     * Test {@see TaxAreaLookupRequestMapper::map()} with an Address
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupRequestMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMapWithAddress(TaxAreaLookupRequestMapperInterface $mapper)
    {
        $address = new Address();
        $address->setStreetAddress(['Line 1', 'Line 2']);
        $address->setPostalCode(99999);
        $address->setCity('Universal City');
        $address->setMainDivision('CA');
        $address->setCountry('USA');

        $request = new Request();
        $request->setPostalAddress($address);

        $map = $mapper->map($request);

        $this->assertTrue(isset($map->TaxAreaRequest->TaxAreaLookup->PostalAddress));
        $address = $map->TaxAreaRequest->TaxAreaLookup->PostalAddress;

        $this->assertEquals('Line 1', $address->StreetAddress1);
        $this->assertEquals('Line 2', $address->StreetAddress2);
        $this->assertEquals(99999, $address->PostalCode);
        $this->assertEquals('Universal City', $address->City);
        $this->assertEquals('CA', $address->MainDivision);
        $this->assertEquals('USA', $address->Country);
    }

    /**
     * Test {@see TaxAreaLookupRequestMapper::map()} with just a TaxAreaId
     *
     * @dataProvider provideMappers
     * @param TaxAreaLookupRequestMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMapWithTaxAreaId(TaxAreaLookupRequestMapperInterface $mapper)
    {
        $taxAreaId = CommonMapperProvider::randBasedOnMethodAvaialbility(
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MIN,
            TaxAreaLookupRequestMapper::TAX_AREA_ID_MAX
        );
        $object = new Request();
        $object->setTaxAreaId($taxAreaId);

        $map = $mapper->map($object);

        $this->assertEquals($taxAreaId, $map->TaxAreaRequest->TaxAreaLookup->TaxAreaId);
    }
}
