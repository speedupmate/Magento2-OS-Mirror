<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api70;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\LineItem;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api70\QuoteRequestMapper;
use Vertex\Mapper\MapperFactory;
use Vertex\Services\Quote\Request;
use Vertex\Services\Quote\RequestInterface;

/**
 * Tests for {@see QuoteResponseMapper}
 */
class QuoteRequestMapperTest extends TestCase
{
    /** @var QuoteRequestMapper */
    private $mapper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(RequestInterface::class, '70');
    }

    /**
     * Test {@see QuoteResponseMapper::map()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testMap()
    {
        $request = new Request();

        $customer = new Customer();
        $request->setCustomer($customer);

        $taxRegistration1 = new TaxRegistration();
        $taxRegistration1->setImpositionType('Use');
        $customer->setTaxRegistrations([$taxRegistration1]);

        $lineItem = new LineItem();
        $lineItemCustomer = new Customer();
        $lineItem->setCustomer($lineItemCustomer);

        $taxRegistration2 = new TaxRegistration();
        $taxRegistration2->setImpositionType('VAT');
        $lineItemCustomer->setTaxRegistrations([$taxRegistration2]);
        $request->setLineItems([$lineItem]);

        $map = $this->mapper->map($request);

        $this->assertIsArray($map->QuotationRequest->Customer->TaxRegistration);
        $this->assertCount(1, $map->QuotationRequest->Customer->TaxRegistration);
        $this->assertEquals('Use', $map->QuotationRequest->Customer->TaxRegistration[0]->impositionType);

        $this->assertIsArray($map->QuotationRequest->LineItem[0]->Customer->TaxRegistration);
        $this->assertCount(1, $map->QuotationRequest->LineItem[0]->Customer->TaxRegistration);
        $this->assertEquals('VAT', $map->QuotationRequest->LineItem[0]->Customer->TaxRegistration[0]->impositionType);
    }
}
