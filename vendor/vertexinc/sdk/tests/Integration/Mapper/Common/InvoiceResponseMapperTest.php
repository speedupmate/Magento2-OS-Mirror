<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\JurisdictionInterface;
use Vertex\Data\TaxInterface;
use Vertex\Mapper\InvoiceResponseMapperInterface;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Services\Invoice\ResponseInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see QuoteResponseMapper}
 */
class InvoiceResponseMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(ResponseInterface::class);
    }

    /**
     * Test {@see QuoteResponseMapper::build()}
     *
     * @dataProvider provideMappers
     * @param InvoiceResponseMapperInterface $mapper
     * @return void
     */
    public function testBuild(InvoiceResponseMapperInterface $mapper)
    {
        $map = $this->createExampleResponseMap();

        $object = $mapper->build($map);

        $this->assertEquals('USD', $object->getCurrencyCode());
        $this->assertEquals('2018-07-03', $object->getDocumentDate()->format('Y-m-d'));
        $this->assertEquals(RequestInterface::TRANSACTION_TYPE_SALE, $object->getTransactionType());
        $this->assertEquals(10, $object->getSubtotal());
        $this->assertEquals(10.8, $object->getTotal());
        $this->assertEquals(0.8, $object->getTotalTax());
        $this->assertCount(2, $object->getLineItems());
        $this->assertEquals('011c945f30ce2cbafc452f39840f025693339c42', $object->getLineItems()[0]->getLineItemId());
        $this->assertEquals('Simple', $object->getLineItems()[0]->getProductCode());
        $this->assertEquals('Taxable Goods', $object->getLineItems()[0]->getProductClass());
        $this->assertEquals(1, $object->getLineItems()[0]->getQuantity());
        $this->assertEquals(5, $object->getLineItems()[0]->getUnitPrice());
        $this->assertEquals(5, $object->getLineItems()[0]->getExtendedPrice());
        $this->assertCount(2, $object->getLineItems()[0]->getTaxes());

        // Line Item 1, Tax 1
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $object->getLineItems()[0]->getTaxes()[0]->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $object->getLineItems()[0]->getTaxes()[0]->getType());
        $this->assertEquals(
            TaxInterface::PARTY_BUYER,
            $object->getLineItems()[0]->getTaxes()[0]->getCollectedFromParty()
        );
        $this->assertEquals(
            JurisdictionInterface::JURISDICTION_LEVEL_STATE,
            $object->getLineItems()[0]->getTaxes()[0]->getJurisdiction()->getLevel()
        );
        $this->assertEquals('31152', $object->getLineItems()[0]->getTaxes()[0]->getJurisdiction()->getId());
        $this->assertEquals('PENNSYLVANIA', $object->getLineItems()[0]->getTaxes()[0]->getJurisdiction()->getName());
        $this->assertEquals(0.3, $object->getLineItems()[0]->getTaxes()[0]->getAmount());
        $this->assertEquals(0.06, $object->getLineItems()[0]->getTaxes()[0]->getEffectiveRate());
        $this->assertEquals([2, 10, 30], $object->getLineItems()[0]->getTaxes()[0]->getInvoiceTextCodes());
        $this->assertEquals(
            TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION,
            $object->getLineItems()[0]->getTaxes()[0]->getVertexTaxCode()
        );
        $this->assertEquals(123, $object->getLineItems()[0]->getTaxes()[0]->getTaxCode());

        // Line Item 1, Tax 2
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $object->getLineItems()[0]->getTaxes()[1]->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $object->getLineItems()[0]->getTaxes()[1]->getType());
        $this->assertEquals(
            TaxInterface::PARTY_BUYER,
            $object->getLineItems()[0]->getTaxes()[1]->getCollectedFromParty()
        );
        $this->assertEquals(
            JurisdictionInterface::JURISDICTION_LEVEL_CITY,
            $object->getLineItems()[0]->getTaxes()[1]->getJurisdiction()->getLevel()
        );
        $this->assertEquals('33126', $object->getLineItems()[0]->getTaxes()[1]->getJurisdiction()->getId());
        $this->assertEquals('PHILADELPHIA', $object->getLineItems()[0]->getTaxes()[1]->getJurisdiction()->getName());
        $this->assertEquals(0.1, $object->getLineItems()[0]->getTaxes()[1]->getAmount());
        $this->assertEquals(0.02, $object->getLineItems()[0]->getTaxes()[1]->getEffectiveRate());

        // Line Item 2, Tax 1
        $this->assertEquals(1, $object->getLineItems()[1]->getQuantity());
        $this->assertEquals(5, $object->getLineItems()[1]->getUnitPrice());
        $this->assertEquals(5, $object->getLineItems()[1]->getExtendedPrice());
        $this->assertCount(2, $object->getLineItems()[1]->getTaxes());
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $object->getLineItems()[1]->getTaxes()[0]->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $object->getLineItems()[1]->getTaxes()[0]->getType());
        $this->assertEquals(
            TaxInterface::PARTY_BUYER,
            $object->getLineItems()[1]->getTaxes()[0]->getCollectedFromParty()
        );
        $this->assertEquals(
            JurisdictionInterface::JURISDICTION_LEVEL_STATE,
            $object->getLineItems()[1]->getTaxes()[0]->getJurisdiction()->getLevel()
        );
        $this->assertEquals('31152', $object->getLineItems()[1]->getTaxes()[0]->getJurisdiction()->getId());
        $this->assertEquals('PENNSYLVANIA', $object->getLineItems()[1]->getTaxes()[0]->getJurisdiction()->getName());
        $this->assertEquals(0.3, $object->getLineItems()[1]->getTaxes()[0]->getAmount());
        $this->assertEquals(0.06, $object->getLineItems()[1]->getTaxes()[0]->getEffectiveRate());

        // Line Item 2, Tax 2
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $object->getLineItems()[1]->getTaxes()[1]->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $object->getLineItems()[1]->getTaxes()[1]->getType());
        $this->assertEquals(
            TaxInterface::PARTY_BUYER,
            $object->getLineItems()[1]->getTaxes()[1]->getCollectedFromParty()
        );
        $this->assertEquals(
            JurisdictionInterface::JURISDICTION_LEVEL_CITY,
            $object->getLineItems()[1]->getTaxes()[1]->getJurisdiction()->getLevel()
        );
        $this->assertEquals('33126', $object->getLineItems()[1]->getTaxes()[1]->getJurisdiction()->getId());
        $this->assertEquals('PHILADELPHIA', $object->getLineItems()[1]->getTaxes()[1]->getJurisdiction()->getName());
        $this->assertEquals(0.1, $object->getLineItems()[1]->getTaxes()[1]->getAmount());
        $this->assertEquals(0.02, $object->getLineItems()[1]->getTaxes()[1]->getEffectiveRate());

        // Commodity Code Line 1
        $this->assertEquals('commodity code', $object->getLineItems()[0]->getCommodityCode());
        $this->assertEquals('commodity code type', $object->getLineItems()[0]->getCommodityCodeType());
    }

    /**
     * Create an example response object
     *
     * @return \stdClass
     */
    private function createExampleResponseMap()
    {
        $map = new \stdClass();
        $map->InvoiceResponse = new \stdClass();
        $map->InvoiceResponse->Currency = new \stdClass();
        $map->InvoiceResponse->Currency->isoCurrencyCodeAlpha = 'USD';
        $map->InvoiceResponse->documentDate = '2018-07-03';
        $map->InvoiceResponse->transactionType = 'SALE';
        $map->InvoiceResponse->SubTotal = 10;
        $map->InvoiceResponse->Total = 10.8;
        $map->InvoiceResponse->TotalTax = 0.8;
        $map->InvoiceResponse->LineItem = [];
        $map->InvoiceResponse->LineItem[0] = $this->getInvoiceLine0();
        $map->InvoiceResponse->LineItem[1] = $this->getInvoiceLine1();

        return $map;
    }

    /**
     * Retrieve an example of invoice line
     *
     * @return \stdClass
     */
    private function getInvoiceLine0()
    {
        $lineItem = new \stdClass();
        $lineItem->lineItemId = '011c945f30ce2cbafc452f39840f025693339c42';
        $lineItem->Product = new \stdClass();
        $lineItem->Product->_ = 'Simple';
        $lineItem->Product->productClass = 'Taxable Goods';
        $lineItem->Quantity = 1.0;
        $lineItem->FairMarketValue = 5.0;
        $lineItem->UnitPrice = 5.0;
        $lineItem->ExtendedPrice = 5.0;
        $lineItem->Taxes = [];
        $lineItem->Taxes[0] = new \stdClass();
        $lineItem->Taxes[0]->taxResult = 'TAXABLE';
        $lineItem->Taxes[0]->taxType = 'SALES';
        $lineItem->Taxes[0]->situs = 'PHYSICAL_ORIGIN';
        $lineItem->Taxes[0]->taxCollectedFromParty = 'BUYER';
        $lineItem->Taxes[0]->Jurisdiction = new \stdClass();
        $lineItem->Taxes[0]->Jurisdiction->jurisdictionLevel = 'STATE';
        $lineItem->Taxes[0]->Jurisdiction->jurisdictionId = '31152';
        $lineItem->Taxes[0]->Jurisdiction->_ = 'PENNSYLVANIA';
        $lineItem->Taxes[0]->CalculatedTax = 0.3;
        $lineItem->Taxes[0]->EffectiveRate = 0.06;
        $lineItem->Taxes[0]->Taxable = 5.0;
        $lineItem->Taxes[0]->Imposition = new \stdClass();
        $lineItem->Taxes[0]->Imposition->impositionType = 'General Sales and Use Tax';
        $lineItem->Taxes[0]->Imposition->_ = 'Sales and Use Tax';
        $lineItem->Taxes[0]->TaxRuleId = 17423;
        $lineItem->Taxes[0]->InvoiceTextCode = [];
        $lineItem->Taxes[0]->InvoiceTextCode[0] = new \stdClass();
        $lineItem->Taxes[0]->InvoiceTextCode[0]->_ = 2;
        $lineItem->Taxes[0]->InvoiceTextCode[1] = new \stdClass();
        $lineItem->Taxes[0]->InvoiceTextCode[1]->_ = 10;
        $lineItem->Taxes[0]->InvoiceTextCode[2] = new \stdClass();
        $lineItem->Taxes[0]->InvoiceTextCode[2]->_ = 30;
        $lineItem->Taxes[0]->vertexTaxCode = TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION;
        $lineItem->Taxes[0]->taxCode = 123;
        $lineItem->Taxes[1] = new \stdClass();
        $lineItem->Taxes[1]->taxResult = 'TAXABLE';
        $lineItem->Taxes[1]->taxType = 'SALES';
        $lineItem->Taxes[1]->situs = 'PHYSICAL_ORIGIN';
        $lineItem->Taxes[1]->taxCollectedFromParty = 'BUYER';
        $lineItem->Taxes[1]->Jurisdiction = new \stdClass();
        $lineItem->Taxes[1]->Jurisdiction->jurisdictionLevel = 'CITY';
        $lineItem->Taxes[1]->Jurisdiction->jurisdictionId = '33126';
        $lineItem->Taxes[1]->Jurisdiction->_ = 'PHILADELPHIA';
        $lineItem->Taxes[1]->CalculatedTax = 0.1;
        $lineItem->Taxes[1]->EffectiveRate = 0.02;
        $lineItem->Taxes[1]->Taxable = 5.0;
        $lineItem->Taxes[1]->Imposition = new \stdClass();
        $lineItem->Taxes[1]->Imposition->impositionType = 'General Sales and Use Tax';
        $lineItem->Taxes[1]->Imposition->_ = 'Local Sales and Use Tax';
        $lineItem->Taxes[1]->TaxRuleId = 286671;
        $lineItem->TotalTax = 0.4;
        $lineItem->CommodityCode = new \stdClass();
        $lineItem->CommodityCode->_ = 'commodity code';
        $lineItem->CommodityCode->commodityCodeType = 'commodity code type';

        return $lineItem;
    }

    /**
     * Retrieve an example of invoice line
     *
     * @return \stdClass
     */
    private function getInvoiceLine1()
    {
        $lineItem = new \stdClass();
        $lineItem->lineItemId = 'shipping';
        $lineItem->Product = new \stdClass();
        $lineItem->Product->_ = 'flatrate_flatrate';
        $lineItem->Product->productClass = 'None';
        $lineItem->Quantity = 1.0;
        $lineItem->FairMarketValue = 5.0;
        $lineItem->UnitPrice = 5.0;
        $lineItem->ExtendedPrice = 5.0;
        $lineItem->Taxes = [];
        $lineItem->Taxes[0] = new \stdClass();
        $lineItem->Taxes[0]->taxResult = 'TAXABLE';
        $lineItem->Taxes[0]->taxType = 'SALES';
        $lineItem->Taxes[0]->situs = 'PHYSICAL_ORIGIN';
        $lineItem->Taxes[0]->taxCollectedFromParty = 'BUYER';
        $lineItem->Taxes[0]->Jurisdiction = new \stdClass();
        $lineItem->Taxes[0]->Jurisdiction->jurisdictionLevel = 'STATE';
        $lineItem->Taxes[0]->Jurisdiction->jurisdictionId = '31152';
        $lineItem->Taxes[0]->Jurisdiction->_ = 'PENNSYLVANIA';
        $lineItem->Taxes[0]->CalculatedTax = 0.3;
        $lineItem->Taxes[0]->EffectiveRate = 0.06;
        $lineItem->Taxes[0]->Taxable = 5.0;
        $lineItem->Taxes[0]->Imposition = new \stdClass();
        $lineItem->Taxes[0]->Imposition->impositionType = 'General Sales and Use Tax';
        $lineItem->Taxes[0]->Imposition->_ = 'Sales and Use Tax';
        $lineItem->Taxes[0]->TaxRuleId = 17423;
        $lineItem->Taxes[1] = new \stdClass();
        $lineItem->Taxes[1]->taxResult = 'TAXABLE';
        $lineItem->Taxes[1]->taxType = 'SALES';
        $lineItem->Taxes[1]->situs = 'PHYSICAL_ORIGIN';
        $lineItem->Taxes[1]->taxCollectedFromParty = 'BUYER';
        $lineItem->Taxes[1]->Jurisdiction = new \stdClass();
        $lineItem->Taxes[1]->Jurisdiction->jurisdictionLevel = 'CITY';
        $lineItem->Taxes[1]->Jurisdiction->jurisdictionId = '33126';
        $lineItem->Taxes[1]->Jurisdiction->_ = 'PHILADELPHIA';
        $lineItem->Taxes[1]->CalculatedTax = 0.1;
        $lineItem->Taxes[1]->EffectiveRate = 0.02;
        $lineItem->Taxes[1]->Taxable = 5.0;
        $lineItem->Taxes[1]->Imposition = new \stdClass();
        $lineItem->Taxes[1]->Imposition->impositionType = 'General Sales and Use Tax';
        $lineItem->Taxes[1]->Imposition->_ = 'Local Sales and Use Tax';
        $lineItem->Taxes[1]->TaxRuleId = 286671;
        $lineItem->TotalTax = 0.4;

        return $lineItem;
    }
}
