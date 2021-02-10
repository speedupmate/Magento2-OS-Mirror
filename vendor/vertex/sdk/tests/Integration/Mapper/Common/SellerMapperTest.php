<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\Seller;
use Vertex\Data\SellerInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\SellerMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see SellerMapper}
 */
class SellerMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(SellerInterface::class);
    }

    /**
     * Test {@see SellerMapper::build()}
     *
     * @dataProvider provideMappers
     * @param SellerMapperInterface $mapper
     * @return void
     */
    public function testBuild(SellerMapperInterface $mapper)
    {
        $map = new \stdClass();
        $map->Company = new \stdClass();
        $map->Company->_ = 'Test';
        $map->PhysicalOrigin = new \stdClass();
        $map->PhysicalOrigin->City = 'Barcelona';
        $map->AdministrativeOrigin = new \stdClass();
        $map->AdministrativeOrigin->City = 'Milan';

        $object = $mapper->build($map);

        $this->assertInstanceOf(SellerInterface::class, $object);
        $this->assertEquals('Test', $object->getCompanyCode());
        $this->assertEquals('Barcelona', $object->getPhysicalOrigin()->getCity());
        $this->assertEquals('Milan', $object->getAdministrativeOrigin()->getCity());
    }

    /**
     * Test {@see SellerMapper::map()}
     *
     * @dataProvider provideMappers
     * @param SellerMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(SellerMapperInterface $mapper)
    {
        $object = new Seller();
        $object->setCompanyCode('Test');
        $address1 = new Address();
        $address1->setCity('Barcelona');
        $object->setPhysicalOrigin($address1);
        $address2 = new Address();
        $address2->setCity('Milan');
        $object->setAdministrativeOrigin($address2);

        $map = $mapper->map($object);

        $this->assertEquals('Test', $map->Company);
        $this->assertEquals('Barcelona', $map->PhysicalOrigin->City);
        $this->assertEquals('Milan', $map->AdministrativeOrigin->City);
    }
}
