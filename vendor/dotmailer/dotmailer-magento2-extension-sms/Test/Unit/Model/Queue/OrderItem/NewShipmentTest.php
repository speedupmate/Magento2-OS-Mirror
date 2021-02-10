<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Model\Queue\OrderItem;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data\ShipmentData;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewShipment;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\OrderItemNotificationEnqueuer;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\TestCase;

class NewShipmentTest extends TestCase
{
    /**
     * @var OrderItemNotificationEnqueuer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderNotificationEnqueuerMock;

    /**
     * @var NewShipment
     */
    private $newShipment;

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
    private $serializerMock;

    /**
     * @var ShipmentData|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentDataMock;

    protected function setUp() :void
    {
        $this->smsOrderNotificationEnqueuerMock = $this->createMock(OrderItemNotificationEnqueuer::class);
        $this->orderInterfaceMock = $this->createMock(OrderInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->shipmentDataMock = $this->createMock(ShipmentData::class);

        $this->newShipment = new NewShipment(
            $this->smsOrderNotificationEnqueuerMock,
            $this->serializerMock,
            $this->loggerMock,
            $this->shipmentDataMock
        );
    }

    public function testQueue()
    {
        $this->orderInterfaceMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn('processing');

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->shipmentDataMock)
            ->willReturn(
                $jsonData = '{"orderStatus": "processing", "trackingNumber": 123456, "trackingCode": Chaz}'
            );

        $this->smsOrderNotificationEnqueuerMock
            ->expects($this->once())
            ->method('queue')
            ->with(
                $this->orderInterfaceMock,
                $jsonData,
                ConfigInterface::XML_PATH_SMS_NEW_SHIPMENT_ENABLED,
                ConfigInterface::SMS_TYPE_NEW_SHIPMENT
            );

        $this->newShipment
            ->buildAdditionalData(
                $this->orderInterfaceMock,
                '123456',
                'Chaz'
            )
            ->queue();
    }
}
