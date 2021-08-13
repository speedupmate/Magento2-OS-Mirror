<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Api70;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\FlexibleNumericField;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api70\LineItemMapper;
use Vertex\Mapper\MapperFactory;

/**
 * Tests for {@see LineItemMapper}
 */
class LineItemMapperTest extends TestCase
{
    /** @var LineItemMapper */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(LineItemInterface::class, '70');
    }

    /**
     * Test {@see LineItemMapper::build()}
     *
     * @return void
     */
    public function testBuild()
    {
        $map = new \stdClass();
        $map->Customer = new \stdClass();
        $map->Customer->CustomerCode = 'Customer #1';
        $map->Customer->TaxRegistration = new \stdClass();
        $map->Customer->TaxRegistration->impositionType = 'Use';

        $object = $this->mapper->build($map);

        $registrations = $object->getCustomer()->getTaxRegistrations();
        $this->assertIsArray($registrations);
        $this->assertCount(1, $registrations);
        $this->assertEquals('Use', $registrations[0]->getImpositionType());
    }

    /**
     * Test {@see LineItemMapper::map()} with invalidly large field id for a numeric flex field
     *
     * @return void
     * @throws ValidationException
     */
    public function testMapWithLargeNumericFlexFieldId()
    {
        $object = new LineItem();
        $flexibleNumericField = new FlexibleNumericField();
        $flexibleNumericField->setFieldId(rand(6, 10));
        $flexibleNumericField->setFieldValue(3.14);
        $object->setFlexibleFields([$flexibleNumericField]);

        $map = $this->mapper->map($object);

        $this->assertCount(1, $map->FlexibleFields->FlexibleNumericField);
        $this->assertEquals(3.14, $map->FlexibleFields->FlexibleNumericField[0]->_);
    }

    /**
     * Test {@see LineItemMapper::map()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testMap()
    {
        $object = new LineItem();

        $customer = new Customer();
        $object->setCustomer($customer);

        $registration = new TaxRegistration();
        $registration->setImpositionType('VAT');
        $customer->setTaxRegistrations([$registration]);

        $map = $this->mapper->map($object);
        $this->assertIsArray($map->Customer->TaxRegistration);
        $this->assertCount(1, $map->Customer->TaxRegistration);
        $this->assertEquals('VAT', $map->Customer->TaxRegistration[0]->impositionType);
    }
}
