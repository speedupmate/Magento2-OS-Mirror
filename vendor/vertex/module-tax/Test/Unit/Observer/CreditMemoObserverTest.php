<?php

namespace Vertex\Tax\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Store\Model\Store;
use Vertex\Services\Invoice\Request;
use Vertex\Services\Invoice\Response;
use Vertex\Services\Invoice\ResponseInterface;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\OrderHasInvoiceDeterminer;
use Vertex\Tax\Model\Repository\OrderInvoiceStatusRepository;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Observer\CreditMemoObserver;
use Vertex\Tax\Observer\GiftwrapExtensionLoader;
use Vertex\Tax\Test\Unit\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CreditMemoObserverTest extends TestCase
{
    /** @var MockObject|Config */
    private $configMock;

    /** @var MockObject|ConfigurationValidator */
    private $configValidatorMock;

    /** @var MockObject|CountryGuard */
    private $countryGuardMock;

    /** @var CreditMemoObserver */
    private $creditMemoObserver;

    /** @var MockObject|ManagerInterface */
    private $managerInterfaceMock;

    /** @var MockObject|TaxInvoice */
    private $taxInvoiceMock;

    /** @var MockObject|InvoiceRequestBuilder */
    private $invoiceRequestBuilderMock;

    /** @var MockObject|OrderHasInvoiceDeterminer */
    private $hasInvoiceDeterminer;

    /** @var MockObject|OrderInvoiceStatusRepository */
    private $orderInvoiceStatusRepositoryMock;

    /** @var MockObject|GiftwrapExtensionLoader */
    private $extensionLoaderMock;

    protected function setUp()
    {
        parent::setUp();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isVertexActive', 'invoiceOrderStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryGuardMock = $this->getMockBuilder(CountryGuard::class)
            ->setMethods(['isOrderServiceableByVertex'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxInvoiceMock = $this->getMockBuilder(TaxInvoice::class)
            ->setMethods(['sendInvoiceRequest', 'sendRefundRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['addSuccessMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configValidatorMock = $this->getMockBuilder(ConfigurationValidator::class)
            ->setMethods(['execute', 'isValid'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = new ConfigurationValidator\Result();
        $result->setValid(true);
        $this->configValidatorMock->method('execute')
            ->willReturn($result);

        $this->invoiceRequestBuilderMock = $this->getMockBuilder(InvoiceRequestBuilder::class)
            ->setMethods(['buildFromCreditmemo'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->hasInvoiceDeterminer = $this->getMockBuilder(OrderHasInvoiceDeterminer::class)
            ->setMethods(['hasinvoice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderInvoiceStatusRepositoryMock = $this->getMockBuilder(OrderInvoiceStatusRepository::class)
            ->setMethods(['getByOrderId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInvoiceStatusRepositoryMock
            ->method('getByOrderId')
            ->willThrowException(new NoSuchEntityException(__('No Such Entity')));

        $this->extensionLoaderMock = $this->getMockBuilder(GiftwrapExtensionLoader::class)
            ->setMethods(['loadOnCreditmemo'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionLoaderMock->method('loadOnCreditmemo')
            ->willReturnCallback(
                static function ($creditMemo) {
                    return $creditMemo;
                }
            );

        $this->creditMemoObserver = $this->getObject(
            CreditMemoObserver::class,
            [
                'config' => $this->configMock,
                'countryGuard' => $this->countryGuardMock,
                'taxInvoice' => $this->taxInvoiceMock,
                'messageManager' => $this->managerInterfaceMock,
                'configValidator' => $this->configValidatorMock,
                'invoiceRequestBuilder' => $this->invoiceRequestBuilderMock,
                'hasInvoiceDeterminer' => $this->hasInvoiceDeterminer,
                'extensionLoader' => $this->extensionLoaderMock,
                'orderInvoiceStatusRepository' => $this->orderInvoiceStatusRepositoryMock,
            ]
        );
    }

    /**
     * Test if creditmemo is sent to Vertex
     *
     * @return void
     */
    public function testSendRefundRequest()
    {
        /** @var MockObject|Event $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getCreditmemo']);

        /** @var MockObject|Observer $observerMock */
        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);

        /** @var MockObject|Store $storeMock */
        $storeMock = $this->createMock(Store::class);

        /** @var MockObject|Order $orderMock */
        $orderMock = $this->createPartialMock(Order::class, ['getStore', 'getId', 'getStatus']);
        $orderMock->method('getStore')->willReturn($storeMock);

        /** @var MockObject|Creditmemo $creditMemoMock */
        $creditMemoMock = $this->createMock(Creditmemo::class);
        $creditMemoMock->method('getOrder')->willReturn($orderMock);

        /** @var MockObject|ResponseInterface $responseMock */
        $responseMock = $this->createPartialMock(Response::class, ['getItems']);
        $responseMock->method('getItems')->willReturn([]);

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $eventMock->expects($this->once())
            ->method('getCreditmemo')
            ->willReturn($creditMemoMock);

        $this->configMock->expects($this->once())
            ->method('isVertexActive')
            ->with(null)
            ->willReturn(true);

        $this->hasInvoiceDeterminer->expects($this->once())
            ->method('hasInvoice')
            ->willReturn(true);

        $this->countryGuardMock->expects($this->once())
            ->method('isOrderServiceableByVertex')
            ->with($orderMock)
            ->willReturn(true);

        $this->configValidatorMock
            ->method('isValid')
            ->willReturn(true);

        $request = new Request();
        $this->invoiceRequestBuilderMock->expects($this->once())
            ->method('buildFromCreditmemo')
            ->with($creditMemoMock)
            ->willReturn($request);

        $this->taxInvoiceMock->expects($this->once())
            ->method('sendRefundRequest')
            ->with($request, $orderMock)
            ->willReturn($responseMock);

        $this->managerInterfaceMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The Vertex invoice has been refunded.')
            ->willReturn($this->managerInterfaceMock);

        $this->creditMemoObserver->execute($observerMock);
    }
}
