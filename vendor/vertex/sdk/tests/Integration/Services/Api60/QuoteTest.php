<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Services\Api60;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\Configuration;
use Vertex\Data\Customer;
use Vertex\Data\JurisdictionInterface;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Data\Login;
use Vertex\Data\Seller;
use Vertex\Data\TaxInterface;
use Vertex\Exception\ApiException;
use Vertex\Exception\ConfigurationException;
use Vertex\Exception\ValidationException;
use Vertex\Services\Quote;
use Vertex\Services\Quote\Request;
use Vertex\Services\Quote\RequestInterface;
use Vertex\Services\Quote\ResponseInterface;
use Vertex\Utility\ServiceActionPerformerFactory;
use Vertex\Utility\SoapClientFactory;

/**
 * Tests for {@see Quote::request()}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteTest extends TestCase
{
    /** @var Quote */
    private $service;

    /** @var ServiceActionPerformerFactory|MockObject */
    private $serviceActionPerformerFactory;

    /** @var SoapClientFactory|MockObject */
    private $soapFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $login = new Login();
        $login->setTrustedId('ABC-EASY-AS-123');

        $configuration = new Configuration();
        $configuration->setLogin($login);
        $configuration->setTaxCalculationWsdl('https://www.example.org/taxCalculation60?wsdl');

        $this->soapFactory = $this->createPartialMock(SoapClientFactory::class, ['create']);
        $this->serviceActionPerformerFactory = $this->createPartialMock(
            ServiceActionPerformerFactory::class,
            ['create']
        );
        $this->serviceActionPerformerFactory->method('create')
            ->willReturnCallback(
                function ($parameters) {
                    $sapFactory = new ServiceActionPerformerFactory();
                    $parameters['soapClientFactory'] = $this->soapFactory;
                    return $sapFactory->create($parameters);
                }
            );
        $this->service = new Quote($configuration, null, null, $this->serviceActionPerformerFactory);
    }

    /**
     * Test the functionality of {@see Quote::request()}
     *
     * @return void
     * @throws ApiException
     * @throws ConfigurationException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function test()
    {
        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['CalculateTax60'])
            ->getMock();
        $soapClient->expects($this->once())
            ->method('CalculateTax60')
            ->with(
                $this->callback(
                    function (\stdClass $stdObject) {
                        return $this->assertRequest($stdObject);
                    }
                )
            )
            ->willReturn($this->getApiResult());

        $this->soapFactory->method('create')->willReturn($soapClient);

        $request = $this->createRequest();
        $response = $this->service->request($request);
        $this->assertEquals('2018-07-12', $response->getDocumentDate()->format('Y-m-d'));
        $this->assertEquals(ResponseInterface::TRANSACTION_TYPE_SALE, $response->getTransactionType());

        $this->assertEquals(100, $response->getSeller()->getCompanyCode());
        $this->assertEquals(['3141 Chestnut Street'], $response->getSeller()->getPhysicalOrigin()->getStreetAddress());
        $this->assertEquals('General', $response->getCustomer()->getCode());
        $this->assertEquals(['233 West Gay St'], $response->getCustomer()->getDestination()->getStreetAddress());
        $this->assertEquals(25, $response->getSubtotal());
        $this->assertEquals(27, $response->getTotal());
        $this->assertEquals(2, $response->getTotalTax());
        $this->assertCount(2, $response->getLineItems());

        /** @var LineItemInterface $lineItem0 */
        $lineItem0 = $response->getLineItems()[0];
        $this->assertEquals('X-MOCK-ITEM', $lineItem0->getProductCode());
        $this->assertEquals('Taxable Goods', $lineItem0->getProductClass());
        $this->assertEquals(2, $lineItem0->getQuantity());
        $this->assertEquals(10, $lineItem0->getUnitPrice());
        $this->assertEquals(20, $lineItem0->getExtendedPrice());
        $this->assertEquals(1.6, $lineItem0->getTotalTax());
        $this->assertCount(2, $lineItem0->getTaxes());

        /** @var TaxInterface $tax0 */
        $tax0 = $lineItem0->getTaxes()[0];
        $this->assertEquals('PENNSYLVANIA', $tax0->getJurisdiction()->getName());
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_STATE, $tax0->getJurisdiction()->getLevel());
        $this->assertEquals(31152, $tax0->getJurisdiction()->getId());
        $this->assertEquals(1.2, $tax0->getAmount());
        $this->assertEquals(0.06, $tax0->getEffectiveRate());
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $tax0->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $tax0->getType());
        $this->assertEquals(TaxInterface::PARTY_BUYER, $tax0->getCollectedFromParty());
        $this->assertEquals('Sales and Use Tax', $tax0->getImposition());
        $this->assertEquals('General Sales and Use Tax', $tax0->getImpositionType());

        /** @var TaxInterface $tax1 */
        $tax1 = $lineItem0->getTaxes()[1];
        $this->assertEquals('PHILADELPHIA', $tax1->getJurisdiction()->getName());
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_CITY, $tax1->getJurisdiction()->getLevel());
        $this->assertEquals(33126, $tax1->getJurisdiction()->getId());
        $this->assertEquals(0.4, $tax1->getAmount());
        $this->assertEquals(0.02, $tax1->getEffectiveRate());
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $tax1->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $tax1->getType());
        $this->assertEquals(TaxInterface::PARTY_BUYER, $tax1->getCollectedFromParty());
        $this->assertEquals('Local Sales and Use Tax', $tax1->getImposition());
        $this->assertEquals('General Sales and Use Tax', $tax1->getImpositionType());

        /** @var LineItemInterface $lineItem1 */
        $lineItem1 = $response->getLineItems()[1];
        $this->assertEquals('flatrate_flatrate', $lineItem1->getProductCode());
        $this->assertEquals('None', $lineItem1->getProductClass());
        $this->assertEquals(1, $lineItem1->getQuantity());
        $this->assertEquals(5, $lineItem1->getUnitPrice());
        $this->assertEquals(5, $lineItem1->getExtendedPrice());
        $this->assertEquals(0.4, $lineItem1->getTotalTax());
        $this->assertCount(2, $lineItem1->getTaxes());

        /** @var TaxInterface $tax0 */
        $tax0 = $lineItem1->getTaxes()[0];
        $this->assertEquals('PENNSYLVANIA', $tax0->getJurisdiction()->getName());
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_STATE, $tax0->getJurisdiction()->getLevel());
        $this->assertEquals(31152, $tax0->getJurisdiction()->getId());
        $this->assertEquals(0.3, $tax0->getAmount());
        $this->assertEquals(0.06, $tax0->getEffectiveRate());
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $tax0->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $tax0->getType());
        $this->assertEquals(TaxInterface::PARTY_BUYER, $tax0->getCollectedFromParty());
        $this->assertEquals('Sales and Use Tax', $tax0->getImposition());
        $this->assertEquals('General Sales and Use Tax', $tax0->getImpositionType());

        /** @var TaxInterface $tax1 */
        $tax1 = $lineItem1->getTaxes()[1];
        $this->assertEquals('PHILADELPHIA', $tax1->getJurisdiction()->getName());
        $this->assertEquals(JurisdictionInterface::JURISDICTION_LEVEL_CITY, $tax1->getJurisdiction()->getLevel());
        $this->assertEquals(33126, $tax1->getJurisdiction()->getId());
        $this->assertEquals(0.1, $tax1->getAmount());
        $this->assertEquals(0.02, $tax1->getEffectiveRate());
        $this->assertEquals(TaxInterface::RESULT_TAXABLE, $tax1->getResult());
        $this->assertEquals(TaxInterface::TYPE_SALES, $tax1->getType());
        $this->assertEquals(TaxInterface::PARTY_BUYER, $tax1->getCollectedFromParty());
        $this->assertEquals('Local Sales and Use Tax', $tax1->getImposition());
        $this->assertEquals('General Sales and Use Tax', $tax1->getImpositionType());
    }

    /**
     * Assert expectations against the request object fed into SOAP
     *
     * @param \stdClass $stdObject
     * @return bool
     */
    private function assertRequest(\stdClass $stdObject)
    {
        $this->assertEquals('ABC-EASY-AS-123', $stdObject->Login->TrustedId);

        $request = $stdObject->QuotationRequest;
        $this->assertEquals('2018-07-12', $request->documentDate);
        $this->assertEquals('SALE', $request->transactionType);
        $this->assertEquals('100', $request->Seller->Company);
        $this->assertEquals('3141 Chestnut Street', $request->Seller->PhysicalOrigin->StreetAddress1);
        $this->assertEquals('Philadelphia', $request->Seller->PhysicalOrigin->City);
        $this->assertEquals('PA', $request->Seller->PhysicalOrigin->MainDivision);
        $this->assertEquals('19104', $request->Seller->PhysicalOrigin->PostalCode);
        $this->assertEquals('USA', $request->Seller->PhysicalOrigin->Country);
        $this->assertEquals('General', $request->Customer->CustomerCode->_);
        $this->assertEquals('Retail Customer', $request->Customer->CustomerCode->classCode);
        $this->assertEquals('233 West Gay St', $request->Customer->Destination->StreetAddress1);
        $this->assertEquals('West Chester', $request->Customer->Destination->City);
        $this->assertEquals('PA', $request->Customer->Destination->MainDivision);
        $this->assertEquals('19382', $request->Customer->Destination->PostalCode);
        $this->assertEquals('USA', $request->Customer->Destination->Country);

        $lineItem0 = $request->LineItem[0];
        $this->assertEquals('0cc0c6ee155bd488fd73cfb2c05d93fc8616f3ed', $lineItem0->lineItemId);
        $this->assertEquals('X-MOCK-ITEM', $lineItem0->Product->_);
        $this->assertEquals('Taxable Goods', $lineItem0->Product->productClass);
        $this->assertEquals(2, $lineItem0->Quantity);
        $this->assertEquals(10, $lineItem0->UnitPrice);
        $this->assertEquals(20, $lineItem0->ExtendedPrice);
        $this->assertFalse(isset($lineItem0->Taxes));

        $lineItem1 = $request->LineItem[1];
        $this->assertEquals('shipping', $lineItem1->lineItemId);
        $this->assertEquals('flatrate_flatrate', $lineItem1->Product->_);
        $this->assertEquals(1, $lineItem1->Quantity);
        $this->assertEquals(5, $lineItem1->UnitPrice);
        $this->assertEquals(5, $lineItem1->ExtendedPrice);

        return true;
    }

    /**
     * Create a sample request object
     *
     * @return Request
     */
    private function createRequest()
    {
        $request = new Request();
        $request->setCurrencyCode('USD');
        $request->setDocumentDate(new \DateTime('2018-07-12'));
        $request->setTransactionType(RequestInterface::TRANSACTION_TYPE_SALE);

        $seller = new Seller();
        $request->setSeller($seller);
        $seller->setCompanyCode('100');
        $sellerAddress = new Address();
        $seller->setPhysicalOrigin($sellerAddress);
        $sellerAddress->setStreetAddress(['3141 Chestnut Street']);
        $sellerAddress->setCity('Philadelphia');
        $sellerAddress->setMainDivision('PA');
        $sellerAddress->setPostalCode('19104');
        $sellerAddress->setCountry('USA');
        $customer = new Customer();
        $request->setCustomer($customer);
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
        $request->setLineItems([$item1, $item2]);

        $item1->setLineItemId('0cc0c6ee155bd488fd73cfb2c05d93fc8616f3ed');
        $item1->setProductCode('X-MOCK-ITEM');
        $item1->setProductClass('Taxable Goods');
        $item1->setQuantity(2);
        $item1->setUnitPrice(10);
        $item1->setExtendedPrice(20);

        $item2->setLineItemId('shipping');
        $item2->setProductCode('flatrate_flatrate');
        $item2->setProductClass('None');
        $item2->setQuantity(1);
        $item2->setUnitPrice(5);
        $item2->setExtendedPrice(5);

        return $request;
    }

    /**
     * Create a sample response object
     *
     * @return \stdClass
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getApiResult()
    {
        $response = new \stdClass();
        $response->Login = new \stdClass();
        $response->Login->TrustedId = '';
        $response->QuotationResponse = new \stdClass();
        $response->QuotationResponse->Currency = new \stdClass();
        $response->QuotationResponse->Currency->isoCurrencyCodeAlpha = 'USD';
        $response->QuotationResponse->Seller = new \stdClass();
        $response->QuotationResponse->Seller->Company = 100;
        $response->QuotationResponse->Seller->PhysicalOrigin = new \stdClass();
        $response->QuotationResponse->Seller->PhysicalOrigin->StreetAddress1 = '3141 Chestnut Street';
        $response->QuotationResponse->Seller->PhysicalOrigin->City = 'Philadelphia';
        $response->QuotationResponse->Seller->PhysicalOrigin->MainDivision = 'PA';
        $response->QuotationResponse->Seller->PhysicalOrigin->PostalCode = '19104';
        $response->QuotationResponse->Seller->PhysicalOrigin->Country = 'USA';
        $response->QuotationResponse->Seller->PhysicalOrigin->taxAreaId = 391013000;
        $response->QuotationResponse->Customer = new \stdClass();
        $response->QuotationResponse->Customer->CustomerCode = new \stdClass();
        $response->QuotationResponse->Customer->CustomerCode->_ = 'General';
        $response->QuotationResponse->Customer->CustomerCode->classCode = 'Retail Customer';
        $response->QuotationResponse->Customer->Destination = new \stdClass();
        $response->QuotationResponse->Customer->Destination->StreetAddress1 = '233 West Gay St';
        $response->QuotationResponse->Customer->Destination->City = 'West Chester';
        $response->QuotationResponse->Customer->Destination->MainDivision = 'PA';
        $response->QuotationResponse->Customer->Destination->PostalCode = '19382';
        $response->QuotationResponse->Customer->Destination->Country = 'USA';
        $response->QuotationResponse->Customer->Destination->taxAreaId = 390294050;
        $response->QuotationResponse->Customer->isTaxExempt = false;
        $response->QuotationResponse->SubTotal = new \stdClass();
        $response->QuotationResponse->SubTotal->_ = '25.0';
        $response->QuotationResponse->Total = new \stdClass();
        $response->QuotationResponse->Total->_ = '27.0';
        $response->QuotationResponse->TotalTax = new \stdClass();
        $response->QuotationResponse->TotalTax->_ = '2.0';
        $response->QuotationResponse->LineItem = [];
        $response->QuotationResponse->LineItem[0] = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Product = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Product->_ = 'X-MOCK-ITEM';
        $response->QuotationResponse->LineItem[0]->Product->productClass = 'Taxable Goods';
        $response->QuotationResponse->LineItem[0]->Quantity = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Quantity->_ = '2.0';
        $response->QuotationResponse->LineItem[0]->FairMarketValue = new \stdClass();
        $response->QuotationResponse->LineItem[0]->FairMarketValue->_ = '20.0';
        $response->QuotationResponse->LineItem[0]->UnitPrice = new \stdClass();
        $response->QuotationResponse->LineItem[0]->UnitPrice->_ = '10.0';
        $response->QuotationResponse->LineItem[0]->ExtendedPrice = new \stdClass();
        $response->QuotationResponse->LineItem[0]->ExtendedPrice->_ = '20.0';
        $response->QuotationResponse->LineItem[0]->Taxes = [];
        $response->QuotationResponse->LineItem[0]->Taxes[0] = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Jurisdiction = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Jurisdiction->_ = 'PENNSYLVANIA';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Jurisdiction->jurisdictionLevel = 'STATE';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Jurisdiction->jurisdictionId = 31152;
        $response->QuotationResponse->LineItem[0]->Taxes[0]->CalculatedTax = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->CalculatedTax->_ = '1.2';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->EffectiveRate = '0.06';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Taxable = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Taxable->_ = '20.0';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Imposition = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Imposition->_ = 'Sales and Use Tax';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->Imposition->impositionType = 'General Sales and Use Tax';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->TaxRuleId = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[0]->TaxRuleId->_ = 17423;
        $response->QuotationResponse->LineItem[0]->Taxes[0]->taxResult = 'TAXABLE';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->taxType = 'SALES';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->situs = 'PHYSICAL_ORIGIN';
        $response->QuotationResponse->LineItem[0]->Taxes[0]->taxCollectedFromParty = 'BUYER';
        $response->QuotationResponse->LineItem[0]->Taxes[1] = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Jurisdiction = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Jurisdiction->_ = 'PHILADELPHIA';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Jurisdiction->jurisdictionLevel = 'CITY';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Jurisdiction->jurisdictionId = 33126;
        $response->QuotationResponse->LineItem[0]->Taxes[1]->CalculatedTax = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->CalculatedTax->_ = '0.4';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->EffectiveRate = '0.02';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Taxable = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Taxable->_ = '20.0';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Imposition = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Imposition->_ = 'Local Sales and Use Tax';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->Imposition->impositionType = 'General Sales and Use Tax';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->TaxRuleId = new \stdClass();
        $response->QuotationResponse->LineItem[0]->Taxes[1]->TaxRuleId->_ = 286671;
        $response->QuotationResponse->LineItem[0]->Taxes[1]->taxResult = 'TAXABLE';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->taxType = 'SALES';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->situs = 'PHYSICAL_ORIGIN';
        $response->QuotationResponse->LineItem[0]->Taxes[1]->taxCollectedFromParty = 'BUYER';
        $response->QuotationResponse->LineItem[0]->TotalTax = new \stdClass();
        $response->QuotationResponse->LineItem[0]->TotalTax->_ = '1.6';
        $response->QuotationResponse->LineItem[0]->lineItemId = '0cc0c6ee155bd488fd73cfb2c05d93fc8616f3ed';
        $response->QuotationResponse->LineItem[1] = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Product = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Product->_ = 'flatrate_flatrate';
        $response->QuotationResponse->LineItem[1]->Product->productClass = 'None';
        $response->QuotationResponse->LineItem[1]->Quantity = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Quantity->_ = '1.0';
        $response->QuotationResponse->LineItem[1]->FairMarketValue = new \stdClass();
        $response->QuotationResponse->LineItem[1]->FairMarketValue->_ = '5.0';
        $response->QuotationResponse->LineItem[1]->UnitPrice = new \stdClass();
        $response->QuotationResponse->LineItem[1]->UnitPrice->_ = '5.0';
        $response->QuotationResponse->LineItem[1]->ExtendedPrice = new \stdClass();
        $response->QuotationResponse->LineItem[1]->ExtendedPrice->_ = '5.0';
        $response->QuotationResponse->LineItem[1]->Taxes = [];
        $response->QuotationResponse->LineItem[1]->Taxes[0] = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Jurisdiction = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Jurisdiction->_ = 'PENNSYLVANIA';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Jurisdiction->jurisdictionLevel = 'STATE';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Jurisdiction->jurisdictionId = 31152;
        $response->QuotationResponse->LineItem[1]->Taxes[0]->CalculatedTax = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->CalculatedTax->_ = '0.3';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->EffectiveRate = '0.06';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Taxable = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Taxable->_ = '5.0';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Imposition = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Imposition->_ = 'Sales and Use Tax';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->Imposition->impositionType = 'General Sales and Use Tax';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->TaxRuleId = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[0]->TaxRuleId->_ = 17423;
        $response->QuotationResponse->LineItem[1]->Taxes[0]->taxResult = 'TAXABLE';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->taxType = 'SALES';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->situs = 'PHYSICAL_ORIGIN';
        $response->QuotationResponse->LineItem[1]->Taxes[0]->taxCollectedFromParty = 'BUYER';
        $response->QuotationResponse->LineItem[1]->Taxes[1] = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Jurisdiction = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Jurisdiction->_ = 'PHILADELPHIA';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Jurisdiction->jurisdictionLevel = 'CITY';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Jurisdiction->jurisdictionId = 33126;
        $response->QuotationResponse->LineItem[1]->Taxes[1]->CalculatedTax = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->CalculatedTax->_ = '0.1';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->EffectiveRate = '0.02';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Taxable = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Taxable->_ = '5.0';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Imposition = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Imposition->_ = 'Local Sales and Use Tax';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->Imposition->impositionType = 'General Sales and Use Tax';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->TaxRuleId = new \stdClass();
        $response->QuotationResponse->LineItem[1]->Taxes[1]->TaxRuleId->_ = 286671;
        $response->QuotationResponse->LineItem[1]->Taxes[1]->taxResult = 'TAXABLE';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->taxType = 'SALES';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->situs = 'PHYSICAL_ORIGIN';
        $response->QuotationResponse->LineItem[1]->Taxes[1]->taxCollectedFromParty = 'BUYER';
        $response->QuotationResponse->LineItem[1]->TotalTax = new \stdClass();
        $response->QuotationResponse->LineItem[1]->TotalTax->_ = '0.4';
        $response->QuotationResponse->LineItem[1]->lineItemId = 'shipping';
        $response->QuotationResponse->documentDate = '2018-07-12';
        $response->QuotationResponse->transactionType = 'SALE';
        $response->ApplicationData = new \stdClass();
        $response->ApplicationData->ResponseTimeMS = '6.3';

        return $response;
    }
}
