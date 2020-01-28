<?php

namespace Vertex\Tax\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Model\Order;
use Vertex\Services\Invoice\Request;
use Vertex\Services\Invoice\Response;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\Data\OrderInvoiceStatus;
use Vertex\Tax\Model\Data\OrderInvoiceStatusFactory;
use Vertex\Tax\Model\ModuleManager;
use Vertex\Tax\Model\Repository\OrderInvoiceStatusRepository;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Observer\GiftwrapExtensionLoader;
use Vertex\Tax\Observer\OrderSavedAfterObserver;
use Vertex\Tax\Observer\ShippingAssignmentExtensionLoader;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Tests for {@see OrderSaveAfterObserver}
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) At limit - doesn't make sense to move success case to drop 1
 */
class OrderSaveAfterObserverTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Config */
    private $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ConfigurationValidator */
    private $configValidatorMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|CountryGuard */
    private $countryGuardMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderInvoiceStatusFactory */
    private $factoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|GiftwrapExtensionLoader */
    private $giftwrapExtensionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface */
    private $managerInterfaceMock;

    /** @var int */
    private $orderId;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderInvoiceStatus */
    private $orderInvoiceStatusMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Order */
    private $orderMock;

    /** @var OrderSavedAfterObserver */
    private $orderSavedAfterObserver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderInvoiceStatusRepository */
    private $repositoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ShippingAssignmentExtensionLoader */
    private $shipmentExtensionLoader;

    /** @var \PHPUnit_Framework_MockObject_MockObject|TaxInvoice */
    private $taxInvoiceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|OrderExtension */
    private $orderExtensionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|InvoiceRequestBuilder */
    private $invoiceRequestBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isVertexActive', 'requestByOrderStatus', 'invoiceOrderStatus', 'isTaxCalculationEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryGuardMock = $this->getMockBuilder(CountryGuard::class)
            ->setMethods(['isOrderServiceableByVertex'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxInvoiceMock = $this->getMockBuilder(TaxInvoice::class)
            ->setMethods(['prepareInvoiceData', 'sendInvoiceRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['addSuccessMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->repositoryMock = $this->getMockBuilder(OrderInvoiceStatusRepository::class)
            ->setMethods(['getByOrderId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock = $this->getMockBuilder(OrderInvoiceStatusFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRequestBuilderMock = $this->getMockBuilder(InvoiceRequestBuilder::class)
            ->setMethods(['buildFromOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInvoiceStatusMock = $this->getMockBuilder(OrderInvoiceStatus::class)
            ->setMethods(['setId', 'setIsSent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock->method('create')
            ->willReturn($this->orderInvoiceStatusMock);

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->setMethods(['getStore', 'getId', 'getStatus', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configValidatorMock = $this->getMockBuilder(ConfigurationValidator::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderExtensionMock = $this->getMockBuilder(OrderExtension::class)
            ->setMethods(['getShippingAssignments'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->giftwrapExtensionMock = $this->getMockBuilder(GiftwrapExtensionLoader::class)
            ->setMethods(['loadOnOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentExtensionLoader = $this->getMockBuilder(ShippingAssignmentExtensionLoader::class)
            ->setMethods(['loadOnOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderId = rand();

        $this->orderMock->method('getId')
            ->willReturn($this->orderId);

        $this->orderMock
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->method('getShippingAssignments')->willReturn(true);

        $this->orderSavedAfterObserver = $this->getObject(
            OrderSavedAfterObserver::class,
            [
                'config' => $this->configMock,
                'countryGuard' => $this->countryGuardMock,
                'taxInvoice' => $this->taxInvoiceMock,
                'messageManager' => $this->managerInterfaceMock,
                'repository' => $this->repositoryMock,
                'factory' => $this->factoryMock,
                'configValidator' => $this->configValidatorMock,
                'invoiceRequestBuilder' => $this->invoiceRequestBuilderMock,
                'giftwrapExtensionLoader' => $this->giftwrapExtensionMock,
                'shipmentExtensionLoader' => $this->shipmentExtensionLoader,
                'showSuccessMessage' => true
            ]
        );
    }

    /**
     * Test that invoice is not sent when configuration is not valid
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testInvoiceNotSentIfConfigurationInvalid()
    {
        $this->assertInvoiceNeverSent();

        $this->setVertexActive();
        $this->setCanService();
        $this->setHasNoInvoice();
        $this->prepareEmptyInvoiceData();
        $this->setupRequestByOrderFlag();
        $this->setConfigValid(false);

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Test that invoice is not sent when order has been invoiced
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     */
    public function testInvoiceNotSentIfOrderAlreadyInvoiced()
    {
        $this->assertInvoiceNeverSent();

        $this->setVertexActive();
        $this->setConfigValid();
        $this->setupRequestByOrderFlag();
        $this->setCanService();
        $this->prepareEmptyInvoiceData();

        // An Invoice is already stored.  Existence is based on non-exception return
        $orderInvoiceStatus = new DataObject();
        $this->repositoryMock->method('getByOrderId')
            ->willReturn($orderInvoiceStatus);

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Test that invoice is not sent when order is not serviceable
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     */
    public function testInvoiceNotSentIfOrderNotServiceable()
    {
        $this->assertInvoiceNeverSent();
        $this->setConfigValid();
        $this->setVertexActive();

        $this->countryGuardMock->expects($this->atLeastOnce())
            ->method('isOrderServiceableByVertex')
            ->willReturn(false);

        $this->setHasNoInvoice();
        $this->prepareEmptyInvoiceData();
        $this->setupRequestByOrderFlag();

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Test that invoice is not sent when order is not right status
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     */
    public function testInvoiceNotSentIfOrderStatusWrong()
    {
        $this->assertInvoiceNeverSent();
        $this->setConfigValid();
        $this->setVertexActive();
        $this->setCanService();
        $this->setHasNoInvoice();
        $this->prepareEmptyInvoiceData();

        $this->configMock->method('requestByOrderStatus')
            ->willReturn(true);

        $status1 = uniqid('order-status-', false);
        $status2 = uniqid('order-status-', false);

        $this->configMock->expects($this->atLeastOnce())
            ->method('invoiceOrderStatus')
            ->willReturn($status1);

        $this->orderMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($status2);

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Test that invoice is not sent when Vertex is not active
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     */
    public function testInvoiceNotSentIfVertexIsNotActive()
    {
        $this->assertInvoiceNeverSent();
        $this->setConfigValid();
        $this->configMock->expects($this->atLeastOnce())
            ->method('isVertexActive')
            ->willReturn(false);

        $this->setCanService();
        $this->setHasNoInvoice();
        $this->prepareEmptyInvoiceData();
        $this->setupRequestByOrderFlag();

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Test that invoice is sent when conditions align
     *
     * @covers \Vertex\Tax\Observer\OrderSavedAfterObserver
     * @return void
     */
    public function testInvoiceSentAndActionsOccur()
    {
        $this->setVertexActive();
        $this->setCanService();
        $this->setHasNoInvoice();
        $this->prepareEmptyInvoiceData();
        $this->setupRequestByOrderFlag();
        $this->setConfigValid();

        $response = new Response();

        // Assert Invoice is Sent
        $this->taxInvoiceMock->expects($this->once())
            ->method('sendInvoiceRequest')
            ->willReturn($response);

        $this->shipmentExtensionLoader->expects($this->once())
            ->method('loadOnOrder')
            ->with(
                $this->callback(
                    function ($order) {
                        return $order->getId() === $this->orderId;
                    }
                )
            )
            ->willReturn($this->orderMock);

        $this->giftwrapExtensionMock->expects($this->once())
            ->method('loadOnOrder')
            ->with(
                $this->callback(
                    function ($order) {
                        return $order->getId() === $this->orderId;
                    }
                )
            )
            ->willReturn($this->orderMock);

        // Assert success message is added
        $this->managerInterfaceMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(
                'The Vertex invoice has been sent.'
            );

        // Assert OrderInvoiceStatus given correct data
        $this->orderInvoiceStatusMock->expects($this->once())
            ->method('setId')
            ->with($this->orderId);
        $this->orderInvoiceStatusMock->expects($this->once())
            ->method('setIsSent')
            ->with(true);

        // Assert OrderInvoiceStatus record saved
        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderInvoiceStatusMock);

        $request = new Request();
        $this->invoiceRequestBuilderMock->expects($this->once())
            ->method('buildFromOrder')
            ->with(
                $this->callback(
                    function ($order) {
                        return $order->getId() === $this->orderId;
                    }
                )
            )
            ->willReturn($request);

        $observer = $this->createObserver();
        $this->orderSavedAfterObserver->execute($observer);
    }

    /**
     * Assert that a Vertex Invoice is never sent
     *
     * @return void
     */
    private function assertInvoiceNeverSent()
    {
        $this->taxInvoiceMock->expects($this->never())
            ->method('sendInvoiceRequest');
    }

    /**
     * Generate the instance of {@see OrderSavedAfterObserver}
     *
     * @return Event\Observer
     */
    private function createObserver()
    {
        $observer = $this->getObject(Event\Observer::class);
        $observer->setData('event', $observer);
        $observer->setData('order', $this->orderMock);
        return $observer;
    }

    /**
     * Ensure that taxInvoice->prepareInvoiceData always returns an array for check before sendInvoiceRequest
     *
     * @return void
     */
    private function prepareEmptyInvoiceData()
    {
        $this->taxInvoiceMock->method('prepareInvoiceData')
            ->willReturn(new Request());
    }

    /**
     * Register that Vertex can/can't service the order
     *
     * @param bool $canService
     * @return void
     */
    private function setCanService($canService = true)
    {
        $this->countryGuardMock->method('isOrderServiceableByVertex')
            ->willReturn($canService);
    }

    /**
     * Register that the Configuration is OK
     *
     * @param bool $configValid
     * @return void
     */
    private function setConfigValid($configValid = true)
    {
        $validatorResult = new ConfigurationValidator\Result();
        $validatorResult->setValid($configValid);

        $this->configValidatorMock->method('execute')
            ->willReturn($validatorResult);
    }

    /**
     * Register that an Order does not have a corresponding Vertex Invoice sent for it
     *
     * @return void
     */
    private function setHasNoInvoice()
    {
        $this->repositoryMock->method('getByOrderId')
            ->willThrowException(new NoSuchEntityException(__('No Such Entity')));
    }

    /**
     * Register that the Vertex module and tax calculation are enabled
     *
     * @return void
     */
    private function setVertexActive()
    {
        $this->configMock->method('isVertexActive')
            ->willReturn(true);

        $this->configMock->method('isTaxCalculationEnabled')
            ->willReturn(true);
    }

    /**
     * Perform setup so that the `$requestByOrder` variable is true
     *
     * Here we create an order status - give it to the order, ensure that the invoiceOrderStatus method on the config
     * will return that status, and set that the requestByOrderStatus config is true.
     *
     * @return void
     */
    private function setupRequestByOrderFlag()
    {
        $status = uniqid('order-status-', false);

        $this->configMock->method('requestByOrderStatus')
            ->willReturn(true);
        $this->configMock->method('invoiceOrderStatus')
            ->willReturn($status);

        $this->orderMock->method('getStatus')
            ->willReturn($status);
    }
}
