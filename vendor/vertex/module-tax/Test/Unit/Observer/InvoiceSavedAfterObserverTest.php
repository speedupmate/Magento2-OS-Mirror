<?php

namespace Vertex\Tax\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\Store;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Vertex\Services\Invoice\Request;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\InvoiceSentRegistry;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Model\Loader\GiftwrapExtensionLoader;
use Vertex\Tax\Observer\InvoiceSavedAfterObserver;
use Vertex\Tax\Model\Loader\VertexCalculationExtensionLoader;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceSavedAfterObserverTest extends TestCase
{
    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|ConfigurationValidator */
    private $configValidatorMock;

    /** @var MockObject|CountryGuard */
    private $countryGuardMock;

    /** @var InvoiceSavedAfterObserver */
    private $invoiceSavedAfterObserver;

    /** @var MockObject|InvoiceSentRegistry */
    private $invoiceSentRegistryMock;

    /** @var MockObject|ManagerInterface */
    private $managerInterfaceMock;

    /** @var MockObject|TaxInvoice */
    private $taxInvoiceMock;

    /** @var MockObject|InvoiceRequestBuilder */
    private $invoiceRequestBuilderMock;

    protected function setUp()
    {
        parent::setUp();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isVertexActive', 'requestByInvoiceCreation', 'isTaxCalculationEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryGuardMock = $this->getMockBuilder(CountryGuard::class)
            ->setMethods(['isOrderServiceableByVertex'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxInvoiceMock = $this->getMockBuilder(TaxInvoice::class)
            ->setMethods(['sendInvoiceRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['addSuccessMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configValidatorMock = $this->getMockBuilder(ConfigurationValidator::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = new ConfigurationValidator\Result();
        $result->setValid(true);

        $this->configValidatorMock->method('execute')
            ->willReturn($result);

        $this->invoiceSentRegistryMock = $this->createMock(InvoiceSentRegistry::class);

        $this->invoiceRequestBuilderMock = $this->getMockBuilder(InvoiceRequestBuilder::class)
            ->setMethods(['buildFromInvoice'])
            ->disableOriginalConstructor()
            ->getMock();

        $vertexExtensionLoaderMock = $this->getMockBuilder(VertexCalculationExtensionLoader::class)
            ->setMethods(['loadOnInvoice'])
            ->disableOriginalConstructor()
            ->getMock();

        $vertexExtensionLoaderMock->method('loadOnInvoice')
            ->willReturnCallback(
                function ($invoice) {
                    return $invoice;
                }
            );

        $giftwrapExtensionLoader = $this->getMockBuilder(GiftwrapExtensionLoader::class)
            ->setMethods(['loadOnInvoice'])
            ->disableOriginalConstructor()
            ->getMock();

        $giftwrapExtensionLoader->method('loadOnInvoice')
            ->willReturnCallback(
                static function ($invoice) {
                    return $invoice;
                }
            );

        $this->invoiceSavedAfterObserver = $this->getObject(
            InvoiceSavedAfterObserver::class,
            [
                'config' => $this->configMock,
                'countryGuard' => $this->countryGuardMock,
                'taxInvoice' => $this->taxInvoiceMock,
                'messageManager' => $this->managerInterfaceMock,
                'invoiceSentRegistry' => $this->invoiceSentRegistryMock,
                'configValidator' => $this->configValidatorMock,
                'invoiceRequestBuilder' => $this->invoiceRequestBuilderMock,
                'vertexExtensionLoader' => $vertexExtensionLoaderMock,
                'showSuccessMessage' => true,
                'extensionLoader' => $giftwrapExtensionLoader
            ]
        );
    }

    public function testNonDuplicativeInvoiceSentState()
    {
        /** @var MockObject|Event $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getInvoice']);

        /** @var MockObject|Event\Observer $observerMock */
        $observerMock = $this->createPartialMock(Event\Observer::class, ['getEvent']);

        /** @var MockObject|Invoice $invoiceMock */
        $invoiceMock = $this->createPartialMock(
            Invoice::class,
            [
                'getStore',
                'getOrder',
                'save'
            ]
        );

        /** @var MockObject|Order $orderMock */
        $orderMock = $this->createMock(Order::class);

        /** @var MockObject|Store $storeMock */
        $storeMock = $this->createMock(Store::class);

        /** @var MockObject|RequestInterface $requestMock */
        $requestMock = $this->createPartialMock(Request::class, []);

        $request = new Request();

        $observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($eventMock);

        $eventMock->expects($this->exactly(2))
            ->method('getInvoice')
            ->willReturn($invoiceMock);

        $invoiceMock->expects($this->exactly(3))
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->invoiceSentRegistryMock->expects($this->exactly(2))
            ->method('hasInvoiceBeenSentToVertex')
            ->with($invoiceMock)
            ->will($this->onConsecutiveCalls(false, true));

        $invoiceMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($storeMock);

        $this->configMock->expects($this->exactly(2))
            ->method('isVertexActive')
            ->with(null)
            ->willReturn(true);

        $this->configMock->expects($this->exactly(2))
            ->method('isTaxCalculationEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->exactly(2))
            ->method('requestByInvoiceCreation')
            ->with($storeMock)
            ->willReturn(true);

        $this->countryGuardMock->expects($this->exactly(2))
            ->method('isOrderServiceableByVertex')
            ->with($orderMock)
            ->willReturn(true);

        $this->taxInvoiceMock->expects($this->once())
            ->method('sendInvoiceRequest')
            ->with($request, $orderMock)
            ->willReturn($requestMock);

        $this->invoiceSentRegistryMock->expects($this->once())
            ->method('setInvoiceHasBeenSentToVertex')
            ->with($invoiceMock)
            ->willReturn($invoiceMock);

        $this->managerInterfaceMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The Vertex invoice has been sent.')
            ->willReturn($this->managerInterfaceMock);

        $this->invoiceRequestBuilderMock->expects($this->once())
            ->method('buildFromInvoice')
            ->with($invoiceMock)
            ->willReturn($request);

        $this->invoiceSavedAfterObserver->execute($observerMock);
        $this->invoiceSavedAfterObserver->execute($observerMock);
    }

    public function testSendOrderAfterInvoiceSaveRequest()
    {
        /** @var MockObject|Event $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getInvoice']);

        /** @var MockObject|Event\Observer $observerMock */
        $observerMock = $this->createPartialMock(Event\Observer::class, ['getEvent']);

        /** @var MockObject|Invoice $invoiceMock */
        $invoiceMock = $this->createPartialMock(
            Invoice::class,
            [
                'getStore',
                'getOrder',
                'save'
            ]
        );

        /** @var MockObject|Order $orderMock */
        $orderMock = $this->createMock(Order::class);

        /** @var MockObject|Store $storeMock */
        $storeMock = $this->createMock(Store::class);

        /** @var MockObject|RequestInterface $requestMock */
        $requestMock = $this->createPartialMock(Request::class, []);

        $request = new Request();

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $eventMock->expects($this->once())
            ->method('getInvoice')
            ->willReturn($invoiceMock);

        $invoiceMock->expects($this->exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->invoiceSentRegistryMock->expects($this->once())
            ->method('hasInvoiceBeenSentToVertex')
            ->with($invoiceMock)
            ->willReturn(false);

        $invoiceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->configMock->expects($this->once())
            ->method('isVertexActive')
            ->with(null)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('isTaxCalculationEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('requestByInvoiceCreation')
            ->with($storeMock)
            ->willReturn(true);

        $this->countryGuardMock->expects($this->once())
            ->method('isOrderServiceableByVertex')
            ->with($orderMock)
            ->willReturn(true);

        $this->taxInvoiceMock->expects($this->once())
            ->method('sendInvoiceRequest')
            ->with($request, $orderMock)
            ->willReturn($requestMock);

        $this->invoiceSentRegistryMock->expects($this->once())
            ->method('setInvoiceHasBeenSentToVertex')
            ->with($invoiceMock)
            ->willReturn($invoiceMock);

        $this->managerInterfaceMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The Vertex invoice has been sent.')
            ->willReturn($this->managerInterfaceMock);

        $this->invoiceRequestBuilderMock->expects($this->once())
            ->method('buildFromInvoice')
            ->with($invoiceMock)
            ->willReturn($request);

        $this->invoiceSavedAfterObserver->execute($observerMock);
    }
}
