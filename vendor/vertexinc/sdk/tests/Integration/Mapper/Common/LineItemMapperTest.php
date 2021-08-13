<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Data\Seller;
use Vertex\Data\Tax;
use Vertex\Data\TaxInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\LineItemMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see LineItemMapper}
 */
class LineItemMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(LineItemInterface::class);
    }

    /**
     * Test {@see LineItemMapper::build()}
     *
     * @dataProvider provideMappers
     * @param LineItemMapperInterface $mapper
     * @return void
     */
    public function testBuild(LineItemMapperInterface $mapper)
    {
        $map = new \stdClass();
        $map->Customer = new \stdClass();
        $map->Customer->CustomerCode = 'Customer #1';
        $map->Seller = new \stdClass();
        $map->Seller->Company = 'Company';
        $map->deliveryTerm = 'SUP';
        $map->lineItemId = 2;
        $map->locationCode = 'location';
        $map->Product = new \stdClass();
        $map->Product->_ = 'SIMPLE';
        $map->Product->productClass = 'Taxable Good';
        $map->Taxes = new \stdClass();
        $map->Taxes->CalculatedTax = 8;
        $map->Taxes->EffectiveRate = 0.8;
        $map->ExtendedPrice = 50.00;
        $map->Quantity = 10;
        $map->TotalTax = 8;
        $map->UnitPrice = 5.00;
        $map->CommodityCode = new \stdClass();
        $map->CommodityCode->_ = 'commodity test code';
        $map->CommodityCode->commodityCodeType = 'commodity type';
        $map->taxIncludedIndicator = true;
        $map->Taxes = [];
        $map->Taxes[] = new \stdClass();
        $map->Taxes[0]->InvoiceTextCode = [];
        $map->Taxes[0]->InvoiceTextCode[0] = new \stdClass();
        $map->Taxes[0]->InvoiceTextCode[0]->_ = 2;
        $map->Taxes[0]->InvoiceTextCode[1] = new \stdClass();
        $map->Taxes[0]->InvoiceTextCode[1]->_ = 10;
        $map->Taxes[0]->InvoiceTextCode[2] = new \stdClass();
        $map->Taxes[0]->InvoiceTextCode[2]->_ = 30;
        $map->Taxes[0]->vertexTaxCode = TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION;
        $map->Taxes[0]->taxCode = 123;

        $object = $mapper->build($map);

        $this->assertEquals('Customer #1', $object->getCustomer()->getCode());
        $this->assertEquals('Company', $object->getSeller()->getCompanyCode());
        $this->assertEquals('SUP', $object->getDeliveryTerm());
        $this->assertEquals(50, $object->getExtendedPrice());
        $this->assertEquals(2, $object->getLineItemId());
        $this->assertEquals('location', $object->getLocationCode());
        $this->assertEquals('SIMPLE', $object->getProductCode());
        $this->assertEquals('Taxable Good', $object->getProductClass());
        $this->assertEquals(10, $object->getQuantity());
        $this->assertEquals(8, $object->getTotalTax());
        $this->assertEquals(5, $object->getUnitPrice());
        $this->assertEquals('commodity test code', $object->getCommodityCode());
        $this->assertEquals('commodity type', $object->getCommodityCodeType());
        $this->assertTrue($object->isTaxIncluded());
        $this->assertEquals([2, 10, 30], $object->getTaxes()[0]->getInvoiceTextCodes());
        $this->assertEquals(123, $object->getTaxes()[0]->getTaxCode());
        $this->assertEquals(
            TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION,
            $object->getTaxes()[0]->getVertexTaxCode()
        );
    }

    /**
     * Test {@see LineItemMapper::map()}
     *
     * @dataProvider provideMappers
     * @param LineItemMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(LineItemMapperInterface $mapper)
    {
        $object = new LineItem();
        $object->setUnitPrice(5.00);
        $object->setQuantity(10);
        $object->setExtendedPrice(50);
        $object->setProductCode('SIMPLE');
        $object->setProductClass('Taxable Good');
        $object->setLocationCode('location');
        $object->setDeliveryTerm('SUP');
        $object->setTaxIncluded(true);

        $customer = new Customer();
        $customer->setCode('Customer #2');
        $customer->setTaxClass('General');
        $object->setCustomer($customer);

        $seller = new Seller();
        $seller->setCompanyCode('companyCode');
        $object->setSeller($seller);

        $object->setLineItemId(2);

        $object->setCommodityCode('commodity test code');
        $object->setCommodityCodeType('commodity type');

        $tax = new Tax();
        $tax->setInvoiceTextCodes([2, 10, 20]);
        $tax->setTaxCode(123);
        $tax->setVertexTaxCode(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION);
        $object->setTaxes([$tax]);

        $map = $mapper->map($object);
        $this->assertEquals(5.00, $map->UnitPrice);
        $this->assertEquals(10, $map->Quantity);
        $this->assertEquals(50, $map->ExtendedPrice);
        $this->assertEquals('SIMPLE', $map->Product->_);
        $this->assertEquals('Taxable Good', $map->Product->productClass);
        $this->assertEquals('location', $map->locationCode);
        $this->assertEquals('SUP', $map->deliveryTerm);
        $this->assertTrue($map->taxIncludedIndicator);
        $this->assertEquals('Customer #2', $map->Customer->CustomerCode->_);
        $this->assertEquals('General', $map->Customer->CustomerCode->classCode);
        $this->assertEquals('companyCode', $map->Seller->Company);
        $this->assertEquals('commodity test code', $map->CommodityCode->_);
        $this->assertEquals('commodity type', $map->CommodityCode->commodityCodeType);
        $this->assertEquals(2, $map->Taxes[0]->InvoiceTextCode[0]->_);
        $this->assertEquals(10, $map->Taxes[0]->InvoiceTextCode[1]->_);
        $this->assertEquals(20, $map->Taxes[0]->InvoiceTextCode[2]->_);
        $this->assertEquals(123, $map->Taxes[0]->taxCode);
        $this->assertEquals(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION, $map->Taxes[0]->vertexTaxCode);
    }
}
