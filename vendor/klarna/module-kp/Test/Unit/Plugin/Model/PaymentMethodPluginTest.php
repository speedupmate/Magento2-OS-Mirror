<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Model;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Api\QuoteInterface;
use Klarna\Kp\Plugin\Model\PaymentMethodPlugin;
use Magento\AdvancedSalesRule\Model\Rule\Condition\FilterTextGenerator\Address\PaymentMethod;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\PaymentMethodPlugin
 */
class PaymentMethodPluginTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var PaymentMethodPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var PaymentMethod|MockObject
     */
    private $subject;

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextKlarnaDisabledReturnUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(false);

        $result = $this->model->afterGenerateFilterText($this->subject, ['input']);
        static::assertSame(['input'], $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextNoQuoteInSessionReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(null);

        $result = $this->model->afterGenerateFilterText($this->subject, ['input']);
        static::assertSame(['input'], $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextNoActiveQuoteReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(1);

        $this->dependencyMocks['quoteRepository']
            ->method('getActiveByQuoteId')
            ->willThrowException(new NoSuchEntityException());

        $result = $this->model->afterGenerateFilterText($this->subject, ['input']);
        static::assertSame(['input'], $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextNoPaymentMethodInArgumentReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(1);

        $quote = $this->mockFactory->create(QuoteInterface::class);
        $quote
            ->method('getPaymentMethods')
            ->willReturn(['klarna_x']);

        $this->dependencyMocks['quoteRepository']
            ->method('getActiveByQuoteId')
            ->willReturn($quote);

        $result = $this->model->afterGenerateFilterText($this->subject, ['some:prefix:value']);
        static::assertSame(['some:prefix:value'], $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextNonKlarnaPaymentMethodInArgumentReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(1);

        $quote = $this->mockFactory->create(QuoteInterface::class);
        $quote
            ->method('getPaymentMethods')
            ->willReturn(['klarna_x']);

        $this->dependencyMocks['quoteRepository']
            ->method('getActiveByQuoteId')
            ->willReturn($quote);

        $result = $this->model->afterGenerateFilterText($this->subject, [
            'quote_address:payment_method:other_payment_provider_method'
        ]);
        static::assertSame(['quote_address:payment_method:other_payment_provider_method'], $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testAfterGenerateFilterTextKlarnaPaymentMethodInArgumentReturnsReplacedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(1);

        $quote = $this->mockFactory->create(QuoteInterface::class);
        $quote
            ->method('getPaymentMethods')
            ->willReturn(['klarna_x']);

        $this->dependencyMocks['quoteRepository']
            ->method('getActiveByQuoteId')
            ->willReturn($quote);

        $result = $this->model->afterGenerateFilterText($this->subject, [
            'quote_address:payment_method:klarna_x'
        ]);
        static::assertSame([
            'quote_address:payment_method:klarna_kp'
        ], $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);

        $this->model = $objectFactory->create(PaymentMethodPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->subject = $this->mockFactory->create(PaymentMethod::class);
    }
}
