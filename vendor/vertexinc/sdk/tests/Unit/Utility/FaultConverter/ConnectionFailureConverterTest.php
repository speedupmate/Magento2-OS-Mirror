<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit\Utility\FaultConverter;

use PHPUnit\Framework\TestCase;
use Vertex\Exception\ApiException\ConnectionFailureException;
use Vertex\Utility\FaultConverter\ConnectionFailureConverter;

/**
 * Tests the functionality of the ConnectionFailureConverter
 */
class ConnectionFailureConverterTest extends TestCase
{
    /** @var ConnectionFailureConverter */
    private $converter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new ConnectionFailureConverter();
    }

    /**
     * Tests that any failure to load the WSDL results in a ConnectionFailureException
     *
     * @return void
     */
    public function testFailureToLoadWsdlConversion()
    {
        $errorMessage = 'SOAP-ERROR: Parsing WSDL: Couldn\'t load from \'' .
            'https://mgsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas60?wsdl\' : failed to load external ' .
            'entity "https://mgsconnect.vertexsmb.com/vertex-ws/services/LookupTaxAreas60?wsdl"';

        $fault = new \SoapFault('WSDL', $errorMessage);

        $result = $this->converter->convert($fault);
        $this->assertInstanceOf(ConnectionFailureException::class, $result);
    }

    /**
     * Tests that a non-WSDL fault returns null
     *
     * @return void
     */
    public function testRandomFaultIsApiRequestException()
    {
        $errorMessage = rand();

        $fault = new \SoapFault('Server', (string)$errorMessage);

        $result = $this->converter->convert($fault);
        $this->assertNull($result);
    }
}
