<?php

namespace Klarna\Kp\Model;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kp\Model\Session
 */
class SessionTest extends TestCase
{
    /** @var \Magento\Checkout\Model\Session | MockInterface */
    private $mockCheckoutSession;

    /** @var Session */
    private $model;

    /** @var \Klarna\Kp\Api\CreditApiInterface | MockInterface */
    private $mockApi;

    /** @var \Klarna\Core\Api\BuilderInterface | MockInterface */
    private $mockBuilder;

    /** @var \Klarna\Kp\Api\QuoteRepositoryInterface | MockInterface */
    private $mockKlarnaQuoteRepository;

    /** @var \Klarna\Kp\Model\QuoteFactory | MockInterface */
    private $mockKlarnaQuoteFactory;

    /**
     * @covers ::setKlarnaQuote()
     * @covers ::getKlarnaQuote()
     */
    public function testKlarnaQuoteAccessors()
    {
        $quoteMock = $this->createMock(\Klarna\Kp\Model\Quote::class);

        $this->model->setKlarnaQuote($quoteMock);
        static::assertEquals($quoteMock, $this->model->getKlarnaQuote());
    }

    protected function setUp(): void
    {
        $this->mockCheckoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->mockApi = $this->createMock(\Klarna\Kp\Api\CreditApiInterface::class);
        $this->mockBuilder = $this->createMock(\Klarna\Core\Api\BuilderInterface::class);
        $this->mockKlarnaQuoteRepository = $this->createMock(\Klarna\Kp\Api\QuoteRepositoryInterface::class);
        $this->mockKlarnaQuoteFactory = $this->getMockBuilder(\Klarna\Kp\Model\QuoteFactory::class)
                                             ->disableOriginalConstructor()
                                             ->setMethods(['create'])->getMock();
        $this->model = new Session(
            $this->mockCheckoutSession,
            $this->mockApi,
            $this->mockBuilder,
            $this->mockKlarnaQuoteRepository,
            $this->mockKlarnaQuoteFactory
        );
    }
}
