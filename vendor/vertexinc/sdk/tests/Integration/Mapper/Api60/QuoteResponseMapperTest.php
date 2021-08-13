<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api60;

use PHPUnit\Framework\TestCase;
use Vertex\Mapper\Api60\QuoteResponseMapper;
use Vertex\Mapper\MapperFactory;
use Vertex\Mapper\QuoteResponseMapperInterface;
use Vertex\Services\Quote\ResponseInterface;

/**
 * Tests for {@see QuoteResponseMapper}
 */
class QuoteResponseMapperTest extends TestCase
{
    /** @var QuoteResponseMapper */
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
     * @param QuoteResponseMapperInterface $mapper
     * @return void
     */
    public function testBuild()
    {
        $map = new \stdClass();
        $map->QuotationResponse = new \stdClass();
        $map->QuotationResponse->Customer = new \stdClass();
        $map->QuotationResponse->Customer->TaxRegistration = new \stdClass();
        $map->QuotationResponse->Customer->TaxRegistration->impositionType = 'Use';
        $map->QuotationResponse->LineItem = [];
        $map->QuotationResponse->LineItem[0] = new \stdClass();
        $map->QuotationResponse->LineItem[0]->lineItemId = '011c945f30ce2cbafc452f39840f025693339c42';
        $map->QuotationResponse->LineItem[0]->Customer = new \stdClass();
        $map->QuotationResponse->LineItem[0]->Customer->TaxRegistration = new \stdClass();
        $map->QuotationResponse->LineItem[0]->Customer->TaxRegistration->impositionType = 'VAT';

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
