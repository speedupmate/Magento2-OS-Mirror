<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\Customer;
use Vertex\Data\LineItem;
use Vertex\Data\Seller;
use Vertex\Data\Tax;
use Vertex\Data\TaxInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\InvoiceRequestMapperInterface;
use Vertex\Services\Invoice\Request;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see QuoteRequestMapper}
 */
class InvoiceRequestMapperTest extends TestCase
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
     * Test {@see QuoteRequestMapper::map()}
     *
     * @dataProvider provideMappers
     * @param InvoiceRequestMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(InvoiceRequestMapperInterface $mapper)
    {
        $object = $this->createTestRequestObject();

        $map = $mapper->map($object);
        $map = $map->InvoiceRequest;
        $this->assertEquals('USD', $map->Currency->isoCurrencyCodeAlpha);
        $this->assertTrue($map->returnAssistedParametersIndicator);
        $this->assertEquals('2018-07-12', $map->documentDate);
        $this->assertEquals(RequestInterface::TRANSACTION_TYPE_SALE, $map->transactionType);
        $this->assertEquals('100', $map->Seller->Company);
        $this->assertEquals('3141 Chestnut Street', $map->Seller->PhysicalOrigin->StreetAddress1);
        $this->assertEquals('Philadelphia', $map->Seller->PhysicalOrigin->City);
        $this->assertEquals('PA', $map->Seller->PhysicalOrigin->MainDivision);
        $this->assertEquals('19104', $map->Seller->PhysicalOrigin->PostalCode);
        $this->assertEquals('USA', $map->Seller->PhysicalOrigin->Country);
        $this->assertEquals('Retail Customer', $map->Customer->CustomerCode->classCode);
        $this->assertEquals('General', $map->Customer->CustomerCode->_);
        $this->assertEquals('233 West Gay St', $map->Customer->Destination->StreetAddress1);
        $this->assertEquals('West Chester', $map->Customer->Destination->City);
        $this->assertEquals('PA', $map->Customer->Destination->MainDivision);
        $this->assertEquals('19382', $map->Customer->Destination->PostalCode);
        $this->assertEquals('USA', $map->Customer->Destination->Country);
        $this->assertEquals('0cc0c6ee155bd488fd73cfb2c05d93fc8616f3ed', $map->LineItem[0]->lineItemId);
        $this->assertEquals('X-MOCK-ITEM', $map->LineItem[0]->Product->_);
        $this->assertEquals('Taxable Goods', $map->LineItem[0]->Product->productClass);
        $this->assertEquals(2, $map->LineItem[0]->Quantity);
        $this->assertEquals(10, $map->LineItem[0]->UnitPrice);
        $this->assertEquals(20, $map->LineItem[0]->ExtendedPrice);
        $this->assertEquals('commodity code', $map->LineItem[0]->CommodityCode->_);
        $this->assertEquals('commodity code type', $map->LineItem[0]->CommodityCode->commodityCodeType);
        $this->assertEquals('shipping', $map->LineItem[1]->lineItemId);
        $this->assertEquals('flatrate_flatrate', $map->LineItem[1]->Product->_);
        $this->assertEquals('None', $map->LineItem[1]->Product->productClass);
        $this->assertEquals(1, $map->LineItem[1]->Quantity);
        $this->assertEquals(5, $map->LineItem[1]->UnitPrice);
        $this->assertEquals(5, $map->LineItem[1]->ExtendedPrice);
        $this->assertEquals(2, $map->LineItem[0]->Taxes[0]->InvoiceTextCode[0]->_);
        $this->assertEquals(10, $map->LineItem[0]->Taxes[0]->InvoiceTextCode[1]->_);
        $this->assertEquals(20, $map->LineItem[0]->Taxes[0]->InvoiceTextCode[2]->_);
        $this->assertEquals(123, $map->LineItem[0]->Taxes[0]->taxCode);
        $this->assertEquals(
            TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION,
            $map->LineItem[0]->Taxes[0]->vertexTaxCode
        );
    }

    /**
     * Create an example request object
     *
     * @return Request
     */
    private function createTestRequestObject()
    {
        $object = new Request();
        $object->setDocumentDate(new \DateTime('2018-07-12'));
        $object->setTransactionType(RequestInterface::TRANSACTION_TYPE_SALE);
        $object->setShouldReturnAssistedParameters(true);

        $object->setCurrencyCode('USD');
        $seller = new Seller();
        $object->setSeller($seller);
        $seller->setCompanyCode('100');
        $sellerAddress = new Address();
        $seller->setPhysicalOrigin($sellerAddress);
        $sellerAddress->setStreetAddress(['3141 Chestnut Street']);
        $sellerAddress->setCity('Philadelphia');
        $sellerAddress->setMainDivision('PA');
        $sellerAddress->setPostalCode('19104');
        $sellerAddress->setCountry('USA');
        $customer = new Customer();
        $object->setCustomer($customer);
        $customer->setCode('General');
        $customer->setTaxClass('Retail Customer');
        $customerAddress = new Address();
        $customer->setDestination($customerAddress);
        $customerAddress->setStreetAddress(['233 West Gay St']);
        $customerAddress->setCity('West Chester');
        $customerAddress->setMainDivision('PA');
        $customerAddress->setPostalCode('19382');
        $customerAddress->setCountry('USA');

        $item1 = new LineItem();
        $item2 = new LineItem();
        $object->setLineItems([$item1, $item2]);

        $item1->setLineItemId('0cc0c6ee155bd488fd73cfb2c05d93fc8616f3ed');
        $item1->setProductCode('X-MOCK-ITEM');
        $item1->setProductClass('Taxable Goods');
        $item1->setQuantity(2);
        $item1->setUnitPrice(10);
        $item1->setExtendedPrice(20);
        $item1->setCommodityCode('commodity code');
        $item1->setCommodityCodeType('commodity code type');

        $taxesItem1 = new Tax();
        $taxesItem1->setInvoiceTextCodes([2, 10, 20]);
        $taxesItem1->setTaxCode(123);
        $taxesItem1->setVertexTaxCode(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION);
        $item1->setTaxes([$taxesItem1]);

        $item2->setLineItemId('shipping');
        $item2->setProductCode('flatrate_flatrate');
        $item2->setProductClass('None');
        $item2->setQuantity(1);
        $item2->setUnitPrice(5);
        $item2->setExtendedPrice(5);
        return $object;
    }
}
