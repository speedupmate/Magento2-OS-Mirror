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
use Vertex\Data\Login;
use Vertex\Data\TaxAreaLookupResult;
use Vertex\Exception\ApiException;
use Vertex\Exception\ConfigurationException;
use Vertex\Exception\ValidationException;
use Vertex\Services\TaxAreaLookup;
use Vertex\Services\TaxAreaLookup\Request;
use Vertex\Utility\ServiceActionPerformerFactory;
use Vertex\Utility\SoapClientFactory;

/**
 * Tests for {@see TaxAreaLookup}
 */
class TaxAreaLookupTest extends TestCase
{
    /** @var TaxAreaLookup */
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
        $configuration->setTaxAreaLookupWsdl('https://www.example.org/taxAreaLookup60?wsdl');

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
        $this->service = new TaxAreaLookup($configuration, null, null, $this->serviceActionPerformerFactory);
    }

    /**
     * Test the functionality of {@see TaxAreaLookup::lookup}
     *
     * @return void
     * @throws ApiException
     * @throws ConfigurationException
     * @throws ValidationException
     */
    public function test()
    {
        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['LookupTaxAreas60'])
            ->getMock();
        $soapClient->expects($this->once())
            ->method('LookupTaxAreas60')
            ->with(
                $this->callback(
                    function ($stdObject) {
                        $addr = $stdObject->TaxAreaRequest->TaxAreaLookup->PostalAddress;
                        return $addr->StreetAddress1 === 'Line 1'
                            && $addr->StreetAddress2 === 'Line 2'
                            && $addr->PostalCode === '99999'
                            && $addr->City === 'Universal City'
                            && $addr->MainDivision === 'CA'
                            && $addr->Country === 'USA';
                    }
                )
            )
            ->willReturn($this->getApiResult());

        $this->soapFactory->method('create')
            ->willReturn($soapClient);

        $request = $this->getApiRequest();
        $response = $this->service->lookup($request);

        $this->assertCount(1, $response->getResults());
        /** @var TaxAreaLookupResult $result */
        $results = $response->getResults();
        $result = reset($results);

        $this->assertCount(1, $result->getPostalAddresses());
        $this->assertCount(1, $result->getStatuses());
        $this->assertCount(5, $result->getJurisdictions());
        $this->assertEquals(100, $result->getConfidenceIndicator());
        $this->assertEquals('50371900', $result->getTaxAreaId());
    }

    /**
     * Retrieve a pre-created API Request
     *
     * @return Request
     */
    private function getApiRequest()
    {
        $address = new Address();
        $address->setStreetAddress(['Line 1', 'Line 2']);
        $address->setPostalCode(99999);
        $address->setCity('Universal City');
        $address->setMainDivision('CA');
        $address->setCountry('USA');

        $request = new Request();
        $request->setPostalAddress($address);

        return $request;
    }

    /**
     * Retrieve a pre-created API Response in SoapClient output format
     *
     * @return \stdClass
     */
    private function getApiResult()
    {
        $map = new \stdClass();
        $map->TaxAreaResponse = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->taxAreaId = '50371900';
        $map->TaxAreaResponse->TaxAreaResult->confidenceIndicator = 100;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction = [];
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->_ = 'UNITED STATES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->jurisdictionLevel = 'COUNTRY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[0]->jurisdictionId = 1;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->_ = 'CALIFORNIA';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->jurisdictionLevel = 'STATE';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[1]->jurisdictionId = 2398;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->_ = 'LOS ANGELES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->jurisdictionLevel = 'COUNTY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[2]->jurisdictionId = 2872;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->_ = 'LOS ANGELES';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->jurisdictionLevel = 'CITY';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[3]->jurisdictionId = 2916;
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4] = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->_ = 'LOS ANGELES TOURISM MARKETING DISTRICT';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->jurisdictionLevel = 'DISTRICT';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->effectiveDate = '1900-01-01';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->expirationDate = '9999-12-31';
        $map->TaxAreaResponse->TaxAreaResult->Jurisdiction[4]->jurisdictionId = 99051;
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->StreetAddress1 = '100 Universal City Plz';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->City = 'Universal City';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->MainDivision = 'CA';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->SubDivision = 'Los Angeles';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->PostalCode = '91608-1002';
        $map->TaxAreaResponse->TaxAreaResult->PostalAddress->Country = 'USA';
        $map->TaxAreaResponse->TaxAreaResult->Status = new \stdClass();
        $map->TaxAreaResponse->TaxAreaResult->Status->lookupResult = 'NORMAL';
        return $map;
    }
}
