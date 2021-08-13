<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\AddressInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\AddressMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see AddressMapper}
 */
class AddressMapperTest extends TestCase
{
    /**
     * Get Address data for testings
     *
     * @return array
     */
    public function getAddressData()
    {
        $address1 = new \stdClass();
        $address1->StreetAddress1 = '233 West Gay St';
        $address1->City = 'West Chester';
        $address1->SubDivision = 'Pennsylvania';
        $address1->MainDivision = 'PA';
        $address1->Country = 'USA';

        $address2 = new \stdClass();
        $address2->StreetAddress1 = 'Line 1';
        $address2->StreetAddress2 = 'Line 2';
        $address2->City = 'City';
        $address2->SubDivision = 'County';
        $address2->MainDivision = 'State';
        $address2->Country = 'Country';
        $address2->PostalCode = 'Postcode';

        return CommonMapperProvider::getAllMappersWithProvidedData(
            AddressInterface::class,
            [
                // Street, City, SubDivision, MainDivision, Country, PostalCode, Map
                'No Postal Code' => [
                    ['233 West Gay St'],
                    'West Chester',
                    'Pennsylvania',
                    'PA',
                    'USA',
                    null,
                    $address1,
                ],
                'Postal Code' => [
                    ['Line 1', 'Line 2'],
                    'City',
                    'County',
                    'State',
                    'Country',
                    'Postcode',
                    $address2,
                ],
                'Empty Address' => [[], null, null, null, null, null, new \stdClass()]
            ]
        );
    }

    /**
     * Test {@see AddressMapper::build()}
     *
     * @dataProvider getAddressData
     * @param AddressMapperInterface $mapper
     * @param string[] $street
     * @param string|null $city
     * @param string|null $subDivision
     * @param string|null $mainDivision
     * @param string|null $country
     * @param string|null $postalCode
     * @param \stdClass $mapping
     * @return void
     */
    public function testBuild(
        AddressMapperInterface $mapper,
        array $street = [],
        $city = null,
        $subDivision = null,
        $mainDivision = null,
        $country = null,
        $postalCode = null,
        \stdClass $mapping = null
    ) {
        $object = $mapper->build($mapping);
        $this->assertEquals($object->getStreetAddress(), $street);
        $this->assertEquals($object->getCity(), $city);
        $this->assertEquals($object->getSubDivision(), $subDivision);
        $this->assertEquals($object->getMainDivision(), $mainDivision);
        $this->assertEquals($object->getCountry(), $country);
        $this->assertEquals($object->getPostalCode(), $postalCode);
    }

    /**
     * Test {@see AddressMapper::map()}
     *
     * @dataProvider getAddressData
     * @param AddressMapperInterface $mapper
     * @param string[] string|null $street
     * @param string|null $city
     * @param string|null $subDivision
     * @param string|null $mainDivision
     * @param string|null $country
     * @param string|null $postalCode
     * @param \stdClass $expectation
     * @return void
     * @throws ValidationException
     */
    public function testMap(
        AddressMapperInterface $mapper,
        array $street = [],
        $city = null,
        $subDivision = null,
        $mainDivision = null,
        $country = null,
        $postalCode = null,
        \stdClass $expectation = null
    ) {
        $object = new Address();
        $object->setStreetAddress($street);
        $object->setCity($city);
        $object->setSubDivision($subDivision);
        $object->setMainDivision($mainDivision);
        $object->setPostalCode($postalCode);
        $object->setCountry($country);

        $map = $mapper->map($object);

        $this->assertEquals($expectation, $map);
    }
}
