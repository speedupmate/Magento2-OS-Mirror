<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api60;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Customer;
use Vertex\Data\LineItem;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\InvoiceRequestMapper;
use Vertex\Mapper\MapperFactory;
use Vertex\Services\Invoice\Request;
use Vertex\Services\Invoice\RequestInterface;

/**
 * Tests for {@see InvoiceRequestMapper}
 */
class InvoiceRequestMapperTest extends TestCase
{
    /** @var InvoiceRequestMapper */
    private $mapper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(RequestInterface::class, '60');
    }

    /**
     * Test {@see InvoiceRequestMapper::map()}
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

        $this->assertIsArray($map->InvoiceRequest->Customer->TaxRegistration);
        $this->assertCount(1, $map->InvoiceRequest->Customer->TaxRegistration);
        $this->assertNotTrue(isset($map->InvoiceRequest->Customer->TaxRegistration[0]->impositionType));

        $this->assertIsArray($map->InvoiceRequest->LineItem[0]->Customer->TaxRegistration);
        $this->assertCount(1, $map->InvoiceRequest->LineItem[0]->Customer->TaxRegistration);
        $this->assertNotTrue(isset($map->InvoiceRequest->LineItem[0]->Customer->TaxRegistration[0]->impositionType));
    }
}
