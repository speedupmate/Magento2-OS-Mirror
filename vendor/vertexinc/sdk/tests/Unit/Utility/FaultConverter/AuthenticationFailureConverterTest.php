<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit\Utility\FaultConverter;

use PHPUnit\Framework\TestCase;
use Vertex\Exception\ApiException\AuthenticationException;
use Vertex\Utility\FaultConverter\AuthenticationFailureConverter;

/**
 * Tests the functionality of the {@see AuthenticationFailureConverter}
 */
class AuthenticationFailureConverterTest extends TestCase
{
    /** @var AuthenticationFailureConverter */
    private $converter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new AuthenticationFailureConverter();
    }

    /**
     * Tests that any failure to load the WSDL results in a ConnectionFailureException
     *
     * @return void
     */
    public function testAuthenticationFailureReturnsException()
    {
        $trustedIdCompanyCode = 'The Trusted ID could not be resolved, please check your connector configuration. ' .
            'Note that Trusted IDs and Company Codes are case sensitive.';

        $fault = new \SoapFault('Server', $trustedIdCompanyCode);

        $result = $this->converter->convert($fault);
        $this->assertInstanceOf(AuthenticationException::class, $result);
    }

    /**
     * Tests that a non-Authentication fault returns null
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
