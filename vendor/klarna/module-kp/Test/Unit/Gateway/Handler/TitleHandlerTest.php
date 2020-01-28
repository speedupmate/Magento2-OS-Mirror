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

namespace Klarna\Kp\Tests\Unit\Gateway;

use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Gateway\Handler\TitleHandler;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;

/**
 * @coversDefaultClass Klarna\Kp\Gateway\Handler\TitleHandler
 */
class TitleHandlerTest extends TestCase
{
    /**
     * @var TitleHandler
     */
    private $titleHandler;
    /**
     * @var InfoInterface|MockObject
     */
    private $infoInterface;
    /**
     * PaymentDataObject|MockObject
     */
    private $paymentDataObject;

    /**
     * Payment set returns payment method title 'Klarna Payments'.
     *
     * @covers ::handle
     */
    public function testHandleWithPayment(): void
    {
        $this->paymentDataObject->method('getPayment')->willReturn($this->infoInterface);

        $actual = $this->titleHandler->handle(['payment' => $this->paymentDataObject]);
        static::assertEquals('Klarna Payments', $actual);
    }

    /**
     * No payment set returns default title 'Klarna Payments'.
     *
     * @covers ::handle
     */
    public function testHandleWithoutPayment(): void
    {
        $actual = $this->titleHandler->handle([]);
        static::assertEquals('Klarna Payments', $actual);
    }

    /**
     * If only method title is set, return title 'Pay Later'.
     *
     * @covers ::getTitle
     */
    public function testGetTitleWithMethodTitle(): void
    {
        $expected = 'Pay Later';

        $this->infoInterface->method('hasAdditionalInformation')->willReturn(true, false);
        $this->infoInterface->method('getAdditionalInformation')->willReturn($expected);

        $actual = $this->titleHandler->getTitle($this->infoInterface);
        static::assertEquals($expected, $actual);
    }

    /**
     * Only method code is set, return title 'Klarna Payments (Pay Later)'.
     *
     * @covers ::getTitle
     */
    public function testGetTitleWithMethodCode(): void
    {
        $this->infoInterface->method('hasAdditionalInformation')->willReturn(false, true, 'Pay Later');
        $this->infoInterface->method('getAdditionalInformation')->willReturn('Pay Later');

        $actual = $this->titleHandler->getTitle($this->infoInterface);
        static::assertEquals('Klarna Payments (Pay Later)', $actual);
    }

    /**
     * Neither method title nor method code is set, return title 'Klarna Payments'.
     *
     * @covers ::getTitle
     */
    public function testGetTitleWithoutMethodTitleAndMethodCode(): void
    {
        $actual = $this->titleHandler->getTitle($this->infoInterface);
        static::assertEquals('Klarna Payments', $actual);
    }

    /**
     * Basic setup for test
     */
    protected function setUp()
    {
        $mockFactory             = new MockFactory();
        $objectFactory           = new TestObjectFactory($mockFactory);
        $this->titleHandler      = $objectFactory->create(TitleHandler::class);
        $this->infoInterface     = $mockFactory->create(InfoInterface::class);
        $this->paymentDataObject = $mockFactory->create(PaymentDataObject::class);
    }
}