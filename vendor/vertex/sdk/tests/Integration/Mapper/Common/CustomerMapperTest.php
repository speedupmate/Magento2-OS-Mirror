<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\Customer;
use Vertex\Data\CustomerInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\CustomerMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see CustomerMapper}
 */
class CustomerMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(CustomerInterface::class);
    }

    /**
     * Test {@see CustomerMapper::build()}
     *
     * @dataProvider provideMappers
     * @param CustomerMapperInterface $customerMapper
     * @return void
     */
    public function testBuild(CustomerMapperInterface $customerMapper)
    {
        $map = new \stdClass();
        $map->CustomerCode = new \stdClass();
        $map->CustomerCode->_ = 'customerCode';
        $map->CustomerCode->classCode = 'customerTaxClass';
        $map->Destination = new \stdClass();
        $map->Destination->StreetAddress1 = 'Line1';
        $map->Destination->City = 'NewCity';
        $map->Destination->MainDivision = 'CA';
        $map->Destination->Country = 'CAN';
        $map->TaxRegistration = new \stdClass();
        $map->TaxRegistration->isoCountryCode = 'CAN';
        $map->TaxRegistration->mainDivision = 'ON';
        $map->TaxRegistration->impositionType = 'Use';

        $object = $customerMapper->build($map);

        $this->assertEquals('customerCode', $object->getCode());
        $this->assertEquals('customerTaxClass', $object->getTaxClass());
        $this->assertEquals(['Line1'], $object->getDestination()->getStreetAddress());
        $this->assertEquals('NewCity', $object->getDestination()->getCity());
        $this->assertEquals('CA', $object->getDestination()->getMainDivision());
        $this->assertEquals('CAN', $object->getDestination()->getCountry());
        $this->assertCount(1, $object->getTaxRegistrations());
        $this->assertEquals('CAN', $object->getTaxRegistrations()[0]->getCountryCode());
        $this->assertEquals('ON', $object->getTaxRegistrations()[0]->getMainDivision());

        $multipleRegistrationMap = new \stdClass();
        $multipleRegistrationMap->TaxRegistration = [];
        $multipleRegistrationMap->TaxRegistration[0] = new \stdClass();
        $multipleRegistrationMap->TaxRegistration[0]->isoCountryCode = 'CAN';
        $multipleRegistrationMap->TaxRegistration[1] = new \stdClass();
        $multipleRegistrationMap->TaxRegistration[1]->isoCountryCode = 'USD';

        $multipleRegistrationObject = $customerMapper->build($multipleRegistrationMap);
        $this->assertCount(2, $multipleRegistrationObject->getTaxRegistrations());
    }

    /**
     * Test {@see CustomerMapper::map()}
     *
     * @dataProvider provideMappers
     * @param CustomerMapperInterface $customerMapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(CustomerMapperInterface $customerMapper)
    {
        $address = new Address();
        $address->setStreetAddress(['Line 1']);
        $address->setCity('City');
        $address->setMainDivision('NY');
        $address->setCountry('USA');

        $customer = new Customer();
        $customer->setTaxClass('taxClass');
        $customer->setCode('customerCode');
        $customer->setDestination($address);

        $registration = new TaxRegistration();
        $registration->setCountryCode('CAN');
        $registration->setImpositionType('Use');
        $registration->setMainDivision('ON');

        $customer->setTaxRegistrations([$registration]);

        $map = $customerMapper->map($customer);

        $this->assertEquals('taxClass', $map->CustomerCode->classCode);
        $this->assertEquals('customerCode', $map->CustomerCode->_);
        $this->assertEquals('Line 1', $map->Destination->StreetAddress1);
        $this->assertEquals('City', $map->Destination->City);
        $this->assertEquals('NY', $map->Destination->MainDivision);
        $this->assertEquals('USA', $map->Destination->Country);
        $this->assertIsArray($map->TaxRegistration);
        $this->assertCount(1, $map->TaxRegistration);
        $this->assertEquals('CAN', $map->TaxRegistration[0]->isoCountryCode);
    }
}
