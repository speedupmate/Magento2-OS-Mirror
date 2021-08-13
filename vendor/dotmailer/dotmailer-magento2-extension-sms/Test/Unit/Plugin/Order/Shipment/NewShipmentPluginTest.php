<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Plugin\Order\Shipment;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewShipment;
use Dotdigitalgroup\Sms\Plugin\Order\Shipment\NewShipmentPlugin;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as NewShipmentAction;
use PHPUnit\Framework\TestCase;

class NewShipmentPluginTest extends TestCase
{
    /**
     * @var Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryInterfaceMock;

    /**
     * @var NewShipmentAction|\PHPUnit\Framework\MockObject\MockObject
     */
    private $newShipmentActionMock;

    /**
     * @var NewShipment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $newShipmentMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestInterfaceMock;

    /**
     * @var NewShipmentPlugin
     */
    private $plugin;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderInterfaceMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->orderRepositoryInterfaceMock = $this->createMock(OrderRepositoryInterface::class);
        $this->newShipmentActionMock = $this->createMock(NewShipmentAction::class);
        $this->newShipmentMock = $this->createMock(NewShipment::class);
        $this->requestInterfaceMock = $this->createMock(RequestInterface::class);
        $this->orderInterfaceMock = $this->createMock(OrderInterface::class);

        $this->plugin = new NewShipmentPlugin(
            $this->loggerMock,
            $this->orderRepositoryInterfaceMock,
            $this->newShipmentMock
        );
    }

    public function testAfterExecuteMethodIfTrackingDefined()
    {
        $this->newShipmentActionMock
            ->expects($this->exactly(2))
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->requestInterfaceMock
            ->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId = 1);

        $this->requestInterfaceMock
            ->expects($this->at(1))
            ->method('getParam')
            ->with('tracking')
            ->willReturn($tracking = [[
                'number' => 35589,
                'carrier_code' => 'chaz',
                'title' => 'Chaz Express'
            ]]);

        $this->orderRepositoryInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderInterfaceMock);

        $this->newShipmentMock
            ->expects($this->once())
            ->method('buildAdditionalData')
            ->with(
                $this->orderInterfaceMock,
                $tracking[0]['number'],
                $tracking[0]['title']
            )
            ->willReturn($this->newShipmentMock);

        $this->newShipmentMock
            ->expects($this->once())
            ->method('queue');

        $this->plugin->afterExecute($this->newShipmentActionMock, []);
    }

    public function testAfterExecuteMethodIfTrackingDidntDefined()
    {
        $this->newShipmentActionMock
            ->expects($this->exactly(2))
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->requestInterfaceMock
            ->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId = 1);

        $this->requestInterfaceMock
            ->expects($this->at(1))
            ->method('getParam')
            ->with('tracking')
            ->willReturn($tracking = null);

        $this->orderRepositoryInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderInterfaceMock);

        $this->newShipmentMock
            ->expects($this->never())
            ->method('buildAdditionalData');

        $this->newShipmentMock
            ->expects($this->never())
            ->method('queue');

        $this->plugin->afterExecute($this->newShipmentActionMock, []);
    }
}
