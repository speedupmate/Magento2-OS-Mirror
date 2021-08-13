<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Jurisdiction;
use Vertex\Data\JurisdictionInterface;
use Vertex\Data\Tax;
use Vertex\Data\TaxInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\TaxMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see TaxMapper}
 */
class TaxMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(TaxInterface::class);
    }

    /**
     * Test {@see TaxMapper::build()}
     *
     * @dataProvider provideMappers
     * @param TaxMapperInterface $mapper
     * @return void
     */
    public function testBuild(TaxMapperInterface $mapper)
    {
        $map = new \stdClass();
        $map->taxResult = TaxInterface::RESULT_TAXABLE;
        $map->taxType = TaxInterface::TYPE_SALES;
        $map->taxCollectedFromParty = TaxInterface::PARTY_BUYER;
        $map->Imposition = new \stdClass();
        $map->Imposition->_ = 'Sales and Use Tax';
        $map->Imposition->impositionType = 'General Sales and Use Tax';
        $map->Jurisdiction = new \stdClass();
        $map->Jurisdiction->jurisdictionLevel = JurisdictionInterface::JURISDICTION_LEVEL_CITY;
        $map->CalculatedTax = 5.00;
        $map->EffectiveRate = 0.10;
        $map->InvoiceTextCode = [];
        $map->InvoiceTextCode[0] = new \stdClass();
        $map->InvoiceTextCode[0]->_ = 2;
        $map->InvoiceTextCode[1] = new \stdClass();
        $map->InvoiceTextCode[1]->_ = 10;
        $map->InvoiceTextCode[2] = new \stdClass();
        $map->InvoiceTextCode[2]->_ = 30;
        $map->vertexTaxCode = TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION;
        $map->taxCode = 123;

        $object = $mapper->build($map);

        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $object->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $object->getType());
        $this->assertEquals(TaxInterface::PARTY_BUYER, $object->getCollectedFromParty());
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_CITY, $object->getJurisdiction()->getLevel());
        $this->assertEquals(5, $object->getAmount());
        $this->assertEquals(.1, $object->getEffectiveRate());
        $this->assertEquals('Sales and Use Tax', $object->getImposition());
        $this->assertEquals('General Sales and Use Tax', $object->getImpositionType());
        $this->assertEquals([2, 10, 30], $object->getInvoiceTextCodes());
        $this->assertEquals(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION, $object->getVertexTaxCode());
        $this->assertEquals(123, $object->getTaxCode());
    }

    /**
     * Test {@see TaxMapper::map()}
     *
     * @dataProvider provideMappers
     * @param TaxMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(TaxMapperInterface $mapper)
    {
        $object = new Tax();
        $object->setCollectedFromParty(TaxInterface::PARTY_BUYER);
        $object->setType(TaxInterface::TYPE_SALES);
        $object->setResult(TaxInterface::RESULT_TAXABLE);
        $object->setImposition('Sales and Use Tax');
        $object->setImpositionType('General Sales and Use Tax');

        $jurisdiction = new Jurisdiction();
        $jurisdiction->setId(6);
        $jurisdiction->setName('Test');
        $jurisdiction->setLevel(JurisdictionInterface::JURISDICTION_LEVEL_CITY);
        $object->setJurisdiction($jurisdiction);
        $object->setAmount(5.00);
        $object->setEffectiveRate(0.10);
        $object->setInvoiceTextCodes([2, 10, 20]);
        $object->setTaxCode(123);
        $object->setVertexTaxCode(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION);

        $map = $mapper->map($object);

        $this->assertEquals(TaxInterface::PARTY_BUYER, $map->taxCollectedFromParty);
        $this->assertEquals(TaxInterface::TYPE_SALES, $map->taxType);
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $map->taxResult);
        $this->assertEquals(6, $map->Jurisdiction->jurisdictionId);
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_CITY, $map->Jurisdiction->jurisdictionLevel);
        $this->assertEquals('Test', $map->Jurisdiction->_);
        $this->assertEquals(5, $map->CalculatedTax);
        $this->assertEquals(0.10, $map->EffectiveRate);
        $this->assertEquals('Sales and Use Tax', $map->Imposition->_);
        $this->assertEquals('General Sales and Use Tax', $map->Imposition->impositionType);
        $this->assertEquals(2, $map->InvoiceTextCode[0]->_);
        $this->assertEquals(10, $map->InvoiceTextCode[1]->_);
        $this->assertEquals(20, $map->InvoiceTextCode[2]->_);
        $this->assertEquals(123, $map->taxCode);
        $this->assertEquals(TaxInterface::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION, $map->vertexTaxCode);
    }
}
