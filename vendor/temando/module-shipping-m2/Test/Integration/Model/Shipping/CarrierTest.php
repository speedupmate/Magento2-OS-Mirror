<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Shipping;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Psr\Log\LogLevel;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Model\Experience;
use Temando\Shipping\Model\Order\OrderReference;
use Temando\Shipping\Model\ResourceModel\Experience\ExperienceRepository;
use Temando\Shipping\Model\ResourceModel\Order\OrderRepository;
use Temando\Shipping\Model\ResourceModel\Shipment\ShipmentRepository;
use Temando\Shipping\Model\Shipment\TrackEventInterface;
use Temando\Shipping\Test\Integration\Provider\RateRequestProvider;
use Temando\Shipping\Webservice\Logger;
use Temando\Shipping\Webservice\Response\Type\OrderResponseTypeInterface;

/**
 * Temando Shipping Carrier Test
 *
 * @magentoAppIsolation enabled
 *
 * @package  Temando\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Delegate provisioning of test data to separate class
     * @return RateRequest[][]
     */
    public function getRateRequest()
    {
        return RateRequestProvider::getRateRequest();
    }

    /**
     * Delegate provisioning of test data to separate class
     * @return RateRequest|OrderResponseTypeInterface[][]
     */
    public function getRateRequestWithShippingExperience()
    {
        return RateRequestProvider::getRateRequestWithShippingExperience();
    }

    /**
     * @test
     */
    public function carrierHasTrackingCapabilities()
    {
        /** @var Carrier $carrier */
        $carrier = Bootstrap::getObjectManager()->create(Carrier::class);

        $this->assertTrue($carrier->isTrackingAvailable());
    }

    /**
     * @test
     */
    public function carrierMethodsAreLoaded()
    {
        /** @var Experience $productionExperience */
        $productionExperience = Bootstrap::getObjectManager()->create(Experience::class, ['data' => [
            Experience::EXPERIENCE_ID => '123',
            Experience::NAME => 'PROD',
            Experience::STATUS => Experience::STATUS_PRODUCTION,
        ]]);
        /** @var Experience $draftExperience */
        $draftExperience = Bootstrap::getObjectManager()->create(Experience::class, ['data' => [
            Experience::EXPERIENCE_ID => '456',
            Experience::NAME => 'DRAFT',
            Experience::STATUS => Experience::STATUS_DRAFT,
        ]]);
        /** @var Experience $disabledExperience */
        $disabledExperience = Bootstrap::getObjectManager()->create(Experience::class, ['data' => [
            Experience::EXPERIENCE_ID => '789',
            Experience::NAME => 'DISABLED',
            Experience::STATUS => Experience::STATUS_DISABLED,
        ]]);
        $experiences = [
            $productionExperience->getExperienceId() => $productionExperience,
            $draftExperience->getExperienceId() => $draftExperience,
            $disabledExperience->getExperienceId() => $disabledExperience
        ];

        $experienceRepository = $this->getMockBuilder(ExperienceRepository::class)
            ->setMethods(['getExperiences'])
            ->disableOriginalConstructor()
            ->getMock();
        $experienceRepository
            ->expects($this->once())
            ->method('getExperiences')
            ->willReturn($experiences);

        /** @var Carrier $carrier */
        $carrier = Bootstrap::getObjectManager()->create(Carrier::class, [
            'experienceRepository' => $experienceRepository,
        ]);

        $allowedMethods = $carrier->getAllowedMethods();
        $this->assertInternalType('array', $allowedMethods);
        $this->assertNotEmpty($allowedMethods);
        $this->assertCount(2, $allowedMethods);

        $this->assertArrayHasKey($productionExperience->getExperienceId(), $allowedMethods);
        $this->assertArrayHasKey($draftExperience->getExperienceId(), $allowedMethods);
        $this->assertArrayNotHasKey($disabledExperience->getExperienceId(), $allowedMethods);
    }

    /**
     * @test
     * @dataProvider getRateRequest
     * @magentoConfigFixture default_store general/store_information/name Foo Store
     *
     * @param RateRequest $rateRequest
     */
    public function collectRatesRepositoryError(RateRequest $rateRequest)
    {
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->once())
            ->method('log')
            ->with($this->equalTo(LogLevel::WARNING));

        $orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new CouldNotSaveException(__('Foo')));

        /** @var Carrier $carrier */
        $carrier = Bootstrap::getObjectManager()->create(Carrier::class, [
            'logger' => $loggerMock,
            'orderRepository' => $orderRepository,
        ]);

        // replace quote by mock
        /** @var Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            $product = Bootstrap::getObjectManager()->create(Product::class);
            $quoteData = $item->getQuote()->getData();
            /** @var Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
            $quote = $this->getMockBuilder(Quote::class)
                ->setMethods(['getShippingAddress', 'getBillingAddress'])
                ->disableOriginalConstructor()
                ->getMock();
            $quote
                ->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($quoteData['shipping_address']);
            $quote
                ->expects($this->any())
                ->method('getBillingAddress')
                ->willReturn($quoteData['billing_address']);
            $quote->setData($quoteData);
            $item->setQuote($quote);
            $item->setData('product', $product);
        }

        $ratesResult = $carrier->collectRates($rateRequest);

        $this->assertTrue($ratesResult->getError());
        $rates = $ratesResult;
        foreach ($rates as $rate) {
            $this->assertInstanceOf(Error::class, $rate);
        }
    }

    /**
     * @test
     * @dataProvider getRateRequestWithShippingExperience
     * @magentoConfigFixture default_store general/store_information/name Foo Store
     *
     * @param RateRequest $rateRequest
     * @param OrderResponseTypeInterface $orderResponseType
     */
    public function collectRatesSuccess(RateRequest $rateRequest, OrderResponseTypeInterface $orderResponseType)
    {
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->setMethods(['log'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock
            ->expects($this->never())
            ->method('log')
            ->with($this->equalTo(LogLevel::WARNING));

        $orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($orderResponseType);

        /** @var Carrier $carrier */
        $carrier = Bootstrap::getObjectManager()->create(Carrier::class, [
            'logger' => $loggerMock,
            'orderRepository' => $orderRepository,
        ]);

        // replace quote by mock
        /** @var Item $item */
        foreach ($rateRequest->getAllItems() as $item) {
            $product = Bootstrap::getObjectManager()->create(Product::class);
            $quoteData = $item->getQuote()->getData();
            /** @var Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
            $quote = $this->getMockBuilder(Quote::class)
                ->setMethods(['getShippingAddress', 'getBillingAddress'])
                ->disableOriginalConstructor()
                ->getMock();
            $quote
                ->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($quoteData['shipping_address']);
            $quote
                ->expects($this->any())
                ->method('getBillingAddress')
                ->willReturn($quoteData['billing_address']);
            $quote->setData($quoteData);
            $item->setQuote($quote);
            $item->setData('product', $product);
        }

        $rates = $carrier->collectRates($rateRequest)->getAllRates();
        $this->assertNotEmpty($rates);
        foreach ($rates as $rate) {
            $this->assertEquals(Carrier::CODE, $rate->getData('carrier'));
        }
    }
}
