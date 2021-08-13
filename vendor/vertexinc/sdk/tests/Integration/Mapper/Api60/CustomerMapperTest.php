<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Api60;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\CustomerInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\CustomerMapper;
use Vertex\Mapper\MapperFactory;

/**
 * Tests for {@see CustomerMapper}
 */
class CustomerMapperTest extends TestCase
{
    /** @var CustomerMapper */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(CustomerInterface::class, '60');
    }

    /**
     * Test {@see CustomerMapper::build()}
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
        $this->assertNull($object->getTaxRegistrations()[0]->getImpositionType());
    }

    /**
     * Test {@see CustomerMapper::map()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testMap()
    {
        $registration = new TaxRegistration();
        $registration->setImpositionType('Use');

        $customer = new Customer();
        $customer->setTaxRegistrations([$registration]);

        $map = $this->mapper->map($customer);

        $this->assertIsArray($map->TaxRegistration);
        $this->assertCount(1, $map->TaxRegistration);
        $this->assertNotTrue(isset($map->TaxRegistration[0]->impositionType));
    }
}
