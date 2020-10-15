<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Kp\Model;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Api\Data\ResponseInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kp\Model\Session
 */
class SessionTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Session
     */
    private $model;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @covers ::setKlarnaQuote()
     * @covers ::getKlarnaQuote()
     */
    public function testKlarnaQuoteAccessors(): void
    {
        $quoteMock = $this->mockFactory->create(\Klarna\Kp\Model\Quote::class);

        $this->model->setKlarnaQuote($quoteMock);
        static::assertEquals($quoteMock, $this->model->getKlarnaQuote());
    }

    /**
     * @covers ::canSendRequest()
     */
    public function testCanSendRequestKpDisabled(): void
    {
        static::assertFalse($this->model->canSendRequest());
    }

    /**
     * @covers ::canSendRequest()
     */
    public function testCanSendRequestKpEnabledButNoKpApiUsed(): void
    {
        $this->dependencyMocks['scopeConfig']->method('isSetFlag')
            ->with('payment/klarna_kp/active', 'stores')
            ->willReturn(true);
        static::assertFalse($this->model->canSendRequest());
    }

    /**
     * @covers ::canSendRequest()
     */
    public function testCanSendRequestKpEnabledAndKpApiUsed(): void
    {
        $this->dependencyMocks['scopeConfig']->method('isSetFlag')
            ->with('payment/klarna_kp/active', 'stores')
            ->willReturn(true);
        $this->dependencyMocks['scopeConfig']->method('getValue')
            ->with('klarna/api/api_version', 'stores', $this->store)
            ->willReturn('kp_na');
        static::assertTrue($this->model->canSendRequest());
    }

    /**
     * @covers ::initWithCartId()
     */
    public function testInitWithCartId(): void
    {
        $apiResponseMock = $this->mockFactory->create(ResponseInterface::class);
        $apiResponseMock->method('isSuccessfull')
            ->willReturn(true);
        $this->model->setApiResponse($apiResponseMock);

        static::assertTrue($this->model->initWithCartId(123, 1)->isSuccessfull());
    }

    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);
        $this->model = $objectFactory->create(Session::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->store = $this->mockFactory->create(Store::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote->method('getStore')
            ->willReturn($this->store);
        $this->dependencyMocks['session']->method('getQuote')
            ->willReturn($quote);
    }
}
