<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Api60;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\FlexibleNumericField;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\LineItemMapper;
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
        $this->mapper = $factory->getForClass(LineItemInterface::class, '60');
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
        $this->assertNull($registrations[0]->getImpositionType());
    }

    /**
     * Test {@see LineItemMapper::map()} with invalidly large field id for a numeric flex field
     *
     * @return void
     * @throws ValidationException
     */
    public function testMapWithLargeNumericFlexFieldId()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field ID must be between 1 and 5, inclusive');
        $object = new LineItem();
        $flexibleNumericField = new FlexibleNumericField();
        $flexibleNumericField->setFieldId(rand(6, 10));
        $flexibleNumericField->setFieldValue(3.14);
        $object->setFlexibleFields([$flexibleNumericField]);

        $this->mapper->map($object);
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
        $this->assertNotTrue(isset($map->Customer->TaxRegistration[0]->impositionType));
    }

    /**
     * Test {@see LineItemMapper::addProductToMap()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testAddProductToMapWithProductCodeAndProductClass()
    {
        $object = new LineItem();
        $object->setProductCode('TOB-123-2');
        $object->setProductClass('FPT');

        $map = $this->mapper->map($object);
        $this->assertNotNull($map->Product->productClass);
        $this->assertEquals('FPT', $map->Product->productClass);
        $this->assertNotNull($map->Product->_);
        $this->assertEquals('TOB-123-2', $map->Product->_);
    }

    /**
     * Test {@see LineItemMapper::addProductToMap()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testAddProductToMapWithoutProductCodeWithProductClass()
    {
        $object = new LineItem();
        $object->setProductClass('FPT');

        $map = $this->mapper->map($object);
        $this->assertNotNull($map->Product->productClass);
        $this->assertEquals('FPT', $map->Product->productClass);
    }
}
