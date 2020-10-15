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

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Model\QuoteRepository;
use Klarna\Kp\Model\Quote as KlarnaQuote;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Gateway\Handler\TitleHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;

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
     * @var Payment|MockObject
     */
    private $payment;
    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDataObject;
    /**
     * @var KlarnaQuote|MockObject
     */
    private $klarnaQuote;
    /**
     * @var QuoteRepository|MockObject
     */
    private $klarnaQuoteRepository;

    /**
     * The fallback 'Klarna Payments' will be returned, if the title can't be determined
     *
     * @covers ::handle
     */
    public function testHandleFallback(): void
    {
        $this->paymentDataObject->method('getPayment')->willReturn($this->payment);
        $actual = $this->titleHandler->handle(['payment' => $this->paymentDataObject]);
        static::assertEquals('Klarna Payments', $actual);
    }

    /**
     * No payment set returns default title 'Klarna Payments'.
     *
     * @covers ::handle
     */
    public function testHandleFallbackWithoutPayment(): void
    {
        $actual = $this->titleHandler->handle([]);
        static::assertEquals('Klarna Payments', $actual);
    }

    /**
     * If only method title is set, return title 'Pay Later'.
     *
     * @covers ::getTitle
     */
    public function testGetTitleFromAdditionalInformation(): void
    {
        $expected = 'Pay Later';
        $this->payment->method('getAdditionalInformation')->willReturn($expected);
        $actual = $this->titleHandler->getTitle($this->payment);
        static::assertEquals($expected, $actual);
    }

    /**
     * Only method code is set, return title 'Pay Later'.
     *
     * @covers ::getTitle
     */
    public function testGetTitleWithMethodCode(): void
    {
        $expected = 'Pay Later';
        $this->payment->method('getMethod')->willReturn('klarna_pay_later');
        $this->klarnaQuote->method('getPaymentMethodInfo')->willReturn(
            [(object) ['name'=>'Pay Later','identifier'=>'pay_now'],(object) ['name'=>'Pay Later','identifier'=>'pay_later']]
        );
        $actual = $this->titleHandler->getTitle($this->payment);
        static::assertEquals($expected, $actual);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory                 = new MockFactory();
        $objectFactory               = new TestObjectFactory($mockFactory);
        $this->titleHandler          = $objectFactory->create(TitleHandler::class);
        $this->paymentDataObject     = $mockFactory->create(PaymentDataObject::class);
        $this->klarnaQuoteRepository = $mockFactory->create(QuoteRepository::class);
        $this->klarnaQuote           = $mockFactory->create(KlarnaQuote::class);
        $this->quote                 = $mockFactory->create(Quote::class);
        $this->payment               = $mockFactory->create(Payment::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();

        $this->dependencyMocks['klarnaQuoteRepository']
            ->expects($this->once())
            ->method('getActiveByQuote')
            ->willReturn($this->klarnaQuote);

        $this->payment
            ->method('getQuote')
            ->willReturn($this->quote);
    }
}
