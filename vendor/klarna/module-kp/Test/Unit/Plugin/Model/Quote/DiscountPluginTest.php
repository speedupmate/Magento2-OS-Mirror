<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Test\Unit\Plugin\Model\Quote;

use Klarna\Core\Model\Config;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Api\QuoteInterface;
use Klarna\Kp\Model\QuoteRepository;
use Klarna\Kp\Plugin\Model\Quote\DiscountPlugin;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\Quote\DiscountPlugin
 */
class DiscountPluginTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var DiscountPlugin
     */
    private $model;
    /**
     * @var Discount|MockObject
     */
    private $subject;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;
    /**
     * @var Config|MockObject
     */
    private $config;
    /**
     * @var Session|MockObject
     */
    private $session;
    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepository;

    /**
     * @var Quote\Address|MockObject
     */
    private $address;
    /**
     * @var string|null
     */
    private $paymentMethodInAddress;

    /**
     * @covers ::beforeCollect()
     */
    public function testBeforeCollectKlarnaDisabledDoesNotChangePaymentMethod()
    {
        $this->setUpBeforeCollectTestCase(
            false,
            false,
            true
        );

        static::assertNull($this->address->getPaymentMethod());
    }

    /**
     * @covers ::beforeCollect()
     */
    public function testBeforeCollectExistingPaymentMethodInAddressDoesNotChange()
    {
        $this->setUpBeforeCollectTestCase(
            true,
            true,
            true
        );

        static::assertSame("already set", $this->address->getPaymentMethod());
    }

    /**
     * @covers ::beforeCollect()
     */
    public function testBeforeCollectPaymentMethodNotInQuotePaymentMethodsDoesNotChangePaymentMethod()
    {
        $this->setUpBeforeCollectTestCase(
            true,
            false,
            false
        );

        static::assertNull($this->address->getPaymentMethod());
    }

    /**
     * @covers ::beforeCollect()
     */
    public function testBeforeCollectChangesAddress()
    {
        $this->setUpBeforeCollectTestCase(
            true,
            false,
            true
        );

        static::assertSame("changeTo", $this->address->getPaymentMethod());
    }

    private function setUpBeforeCollectTestCase(
        bool $klarnaEnabled,
        bool $isAddressPaymentMethodSet,
        bool $isPaymentMethodInQuotePaymentMethods
    ) {
        $shippingAssignment = $this->mockFactory->create(ShippingAssignmentInterface::class);
        $total = $this->mockFactory->create(Total::class);

        $this->address = $this->mockFactory->create(Quote\Address::class, [
            'getPaymentMethod',
            'setPaymentMethod'
        ]);
        $this->address
            ->method('getPaymentMethod')
            ->willReturnCallback(function () {
                return $this->paymentMethodInAddress;
            });
        $this->address
            ->method('setPaymentMethod')
            ->willReturnCallback(function ($setTo) {
                $this->paymentMethodInAddress = $setTo;
            });

        if ($isAddressPaymentMethodSet) {
            $this->paymentMethodInAddress = "already set";
        }

        $shipping = $this->mockFactory->create(ShippingInterface::class);

        $shippingAssignment
            ->method('getShipping')
            ->willReturn($shipping);

        $shipping
            ->method('getAddress')
            ->willReturn($this->address);

        $this->request
            ->method('getContent')
            ->willReturn("{\"paymentMethod\":{\"method\":\"changeTo\"}}");

        $this->session
            ->method('getQuoteId')
            ->willReturn(1);

        $quote = $this->mockFactory->create(QuoteInterface::class);
        $quote
            ->method('getPaymentMethods')
            ->willReturn($isPaymentMethodInQuotePaymentMethods ? ['changeTo'] : []);
        $this->quoteRepository
            ->method('getActiveByQuoteId')
            ->willReturn($quote);

        $this->config
            ->method('klarnaEnabled')
            ->willReturn($klarnaEnabled);

        $this->model->beforeCollect(
            $this->subject,
            $this->mockFactory->create(Quote::class),
            $shippingAssignment,
            $total
        );
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);

        $this->request = $this->mockFactory->create(Http::class);
        $this->config = $this->mockFactory->create(Config::class);
        $this->session = $this->mockFactory->create(Session::class);
        $this->quoteRepository = $this->mockFactory->create(QuoteRepository::class);

        $this->model = $objectFactory->create(DiscountPlugin::class);
        $this->model = new DiscountPlugin(
            $this->request,
            $this->config,
            $this->session,
            $this->quoteRepository
        );

        $this->subject = $this->mockFactory->create(Discount::class);

        $this->address = null;
    }
}
