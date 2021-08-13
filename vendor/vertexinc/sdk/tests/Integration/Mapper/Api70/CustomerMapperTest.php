<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api70;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\CustomerInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api70\CustomerMapper;
use Vertex\Mapper\MapperFactory;

/**
 * Tests for {@see CustomerMapper}
 */
class CustomerMapperTest extends TestCase
{
    /** @var CustomerMapper */
    private $mapper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(CustomerInterface::class, '70');
    }

    /**
     * Test existence of TaxRegistration impositionType in built object
     *
     * @return void
     */
    public function testBuild()
    {
        $map = new \stdClass();
        $map->TaxRegistration = new \stdClass();
        $map->TaxRegistration->impositionType = 'Use';

        $object = $this->mapper->build($map);
        $this->assertCount(1, $object->getTaxRegistrations());
        $this->assertEquals('Use', $object->getTaxRegistrations()[0]->getImpositionType());

        $mapOfMultiple = new \stdClass();
        $mapOfMultiple->TaxRegistration = [];
        $mapOfMultiple->TaxRegistration[0] = new \stdClass();
        $mapOfMultiple->TaxRegistration[0]->impositionType = 'VAT';
        $mapOfMultiple->TaxRegistration[1] = new \stdClass();
        $mapOfMultiple->TaxRegistration[1]->impositionType = 'Use';

        $objectOfMultiple = $this->mapper->build($mapOfMultiple);
        $this->assertCount(2, $objectOfMultiple->getTaxRegistrations());
        $this->assertEquals('VAT', $objectOfMultiple->getTaxRegistrations()[0]->getImpositionType());
        $this->assertEquals('Use', $objectOfMultiple->getTaxRegistrations()[1]->getImpositionType());
    }

    /**
     * Test existence of TaxRegistration impositionType in mapping
     *
     * @return void
     * @throws ValidationException
     */
    public function testMap()
    {
        $registration = new TaxRegistration();
        $registration->setImpositionType('VAT');

        $customer = new Customer();
        $customer->setTaxRegistrations([$registration]);

        $map = $this->mapper->map($customer);

        $this->assertIsArray($map->TaxRegistration);
        $this->assertCount(1, $map->TaxRegistration);
        $this->assertEquals('VAT', $map->TaxRegistration[0]->impositionType);
    }
}
