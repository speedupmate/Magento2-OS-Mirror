<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Model\Queue\OrderItem;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data\OrderData;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\OrderItemNotificationEnqueuer;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\UpdateOrder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\TestCase;

class UpdateOrderTest extends TestCase
{
    /**
     * @var OrderItemNotificationEnqueuer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderNotificationEnqueuerMock;

    /**
     * @var UpdateOrder
     */
    private $updateOrder;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderInterfaceMock;

    /**
     * @var Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializeInterfaceMock;

    /**
     * @var OrderData|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderDataMock;

    protected function setUp() :void
    {
        $this->smsOrderNotificationEnqueuerMock = $this->createMock(OrderItemNotificationEnqueuer::class);
        $this->orderInterfaceMock = $this->createMock(OrderInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->serializeInterfaceMock = $this->createMock(SerializerInterface::class);
        $this->orderDataMock = $this->createMock(OrderData::class);

        $this->updateOrder = new UpdateOrder(
            $this->smsOrderNotificationEnqueuerMock,
            $this->serializeInterfaceMock,
            $this->loggerMock,
            $this->orderDataMock
        );
    }

    public function testQueueIfOrderIsCanceled()
    {
        $this->orderInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn('canceled');

        $this->serializeInterfaceMock->expects($this->once())
            ->method('serialize')
            ->with($this->orderDataMock)
            ->willReturn($jsonData = '{"orderStatus": "canceled"}');

        $this->smsOrderNotificationEnqueuerMock
            ->expects($this->once())
            ->method('queue')
            ->with(
                $this->orderInterfaceMock,
                $jsonData,
                ConfigInterface::XML_PATH_SMS_ORDER_UPDATE_ENABLED,
                ConfigInterface::SMS_TYPE_UPDATE_ORDER
            );

        $this->updateOrder
            ->buildAdditionalData($this->orderInterfaceMock)
            ->queue();
    }

    public function testQueueIfOrderIsHolded()
    {
        $this->orderInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn('holded');

        $this->serializeInterfaceMock->expects($this->once())
            ->method('serialize')
            ->with($this->orderDataMock)
            ->willReturn($jsonData = '{"orderStatus": "holded"}');

        $this->smsOrderNotificationEnqueuerMock
            ->expects($this->once())
            ->method('queue')
            ->with(
                $this->orderInterfaceMock,
                $jsonData,
                ConfigInterface::XML_PATH_SMS_ORDER_UPDATE_ENABLED,
                ConfigInterface::SMS_TYPE_UPDATE_ORDER
            );

        $this->updateOrder
            ->buildAdditionalData($this->orderInterfaceMock)
            ->queue();
    }
}
