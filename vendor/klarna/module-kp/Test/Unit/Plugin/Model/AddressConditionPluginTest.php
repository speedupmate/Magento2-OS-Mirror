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
use Klarna\Kp\Plugin\Model\AddressConditionPlugin;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule\Condition\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\AddressConditionPlugin
 */
class AddressConditionPluginTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var AddressConditionPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var Address|MockObject
     */
    private $subject;

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testBeforeValidateAttributeKlarnaDisabledReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(false);

        $result = $this->model->beforeValidateAttribute($this->subject, "input");
        static::assertSame("input", $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testBeforeValidateAttributeNoQuoteInSessionReturnsUnchangedInput(): void
    {
        $this->dependencyMocks['config']
            ->method('klarnaEnabled')
            ->willReturn(true);

        $this->dependencyMocks['session']
            ->method('getQuoteId')
            ->willReturn(null);

        $result = $this->model->beforeValidateAttribute($this->subject, "input");
        static::assertSame("input", $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testBeforeValidateAttributeNoActiveQuoteReturnsUnchangedInput(): void
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

        $result = $this->model->beforeValidateAttribute($this->subject, "input");
        static::assertSame("input", $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testBeforeValidateAttributeUnmatchedPaymentMethodsReturnsUnchangedInput(): void
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

        $result = $this->model->beforeValidateAttribute($this->subject, "input");
        static::assertSame("input", $result);
    }

    /**
     * @covers ::beforeValidateAttribute()
     */
    public function testBeforeValidateAttributeReturnsReplacedInput(): void
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

        $result = $this->model->beforeValidateAttribute($this->subject, "klarna_x");
        static::assertSame("klarna_kp", $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);

        $this->model = $objectFactory->create(AddressConditionPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->subject = $this->mockFactory->create(Address::class);
    }
}
