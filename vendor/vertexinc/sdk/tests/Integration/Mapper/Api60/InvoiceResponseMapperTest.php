<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api60;

use PHPUnit\Framework\TestCase;
use Vertex\Mapper\Api60\InvoiceResponseMapper;
use Vertex\Mapper\MapperFactory;
use Vertex\Services\Invoice\ResponseInterface;

/**
 * Tests for {@see InvoiceResponseMapper}
 */
class InvoiceResponseMapperTest extends TestCase
{
    /** @var InvoiceResponseMapper */
    private $mapper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(ResponseInterface::class, '60');
    }

    /**
     * Test {@see QuoteResponseMapper::build()}
     *
     * @return void
     */
    public function testBuild()
    {
        $map = new \stdClass();
        $map->InvoiceResponse = new \stdClass();
        $map->InvoiceResponse->Customer = new \stdClass();
        $map->InvoiceResponse->Customer->TaxRegistration = new \stdClass();
        $map->InvoiceResponse->Customer->TaxRegistration->impositionType = 'Use';
        $map->InvoiceResponse->LineItem = [];
        $map->InvoiceResponse->LineItem[0] = new \stdClass();
        $map->InvoiceResponse->LineItem[0]->lineItemId = '011c945f30ce2cbafc452f39840f025693339c42';
        $map->InvoiceResponse->LineItem[0]->Customer = new \stdClass();
        $map->InvoiceResponse->LineItem[0]->Customer->TaxRegistration = new \stdClass();
        $map->InvoiceResponse->LineItem[0]->Customer->TaxRegistration->impositionType = 'VAT';

        $object = $this->mapper->build($map);

        $registrations = $object->getLineItems()[0]->getCustomer()->getTaxRegistrations();

        $this->assertIsArray($registrations);
        $this->assertCount(1, $registrations);
        $this->assertNull($registrations[0]->getImpositionType());

        $registrations = $object->getCustomer()->getTaxRegistrations();

        $this->assertIsArray($registrations);
        $this->assertCount(1, $registrations);
        $this->assertNull($registrations[0]->getImpositionType());
    }
}
