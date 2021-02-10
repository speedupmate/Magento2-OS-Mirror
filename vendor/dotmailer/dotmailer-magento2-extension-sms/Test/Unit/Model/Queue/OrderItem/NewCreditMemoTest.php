<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Model\Queue\OrderItem;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewCreditMemo;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\OrderItemNotificationEnqueuer;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Api\Data\OrderInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data\CreditMemoData;
use PHPUnit\Framework\TestCase;

class NewCreditMemoTest extends TestCase
{
    /**
     * @var OrderItemNotificationEnqueuer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderNotificationEnqueuerMock;

    /**
     * @var NewCreditMemo
     */
    private $newCreditMemo;

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
     * @var PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $priceCurrencyInterfaceMock;

    /**
     * @var Creditmemo|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditMemoMock;

    /**
     * @var CreditMemoData|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditMemoDataMock;

    protected function setUp() :void
    {
        $this->smsOrderNotificationEnqueuerMock = $this->createMock(OrderItemNotificationEnqueuer::class);
        $this->orderInterfaceMock = $this->createMock(OrderInterface::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->priceCurrencyInterfaceMock = $this->createMock(PriceCurrencyInterface::class);
        $this->creditMemoMock = $this->createMock(Creditmemo::class);
        $this->creditMemoDataMock = $this->createMock(CreditMemoData::class);

        $this->newCreditMemo = new NewCreditMemo(
            $this->smsOrderNotificationEnqueuerMock,
            $this->serializerMock,
            $this->loggerMock,
            $this->priceCurrencyInterfaceMock,
            $this->creditMemoDataMock
        );
    }

    public function testQueue()
    {
        $this->orderInterfaceMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn('completed');

        $this->creditMemoMock
            ->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal = "25");

        $this->creditMemoMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId = 1);

        $this->creditMemoMock
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn($currencyCode = "USD");

        $this->priceCurrencyInterfaceMock
            ->expects($this->once())
            ->method('format')
            ->with(
                $grandTotal,
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $currencyCode
            )
            ->willReturn("$25");

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->creditMemoDataMock)
            ->willReturn($jsonData = '{"orderStatus": "complete","creditMemoAmount": "$25"}');

        $this->smsOrderNotificationEnqueuerMock
            ->expects($this->once())
            ->method('queue')
            ->with(
                $this->orderInterfaceMock,
                $jsonData,
                ConfigInterface::XML_PATH_SMS_NEW_CREDIT_MEMO_ENABLED,
                ConfigInterface::SMS_TYPE_NEW_CREDIT_MEMO
            );

        $this->newCreditMemo
            ->buildAdditionalData(
                $this->orderInterfaceMock,
                $this->creditMemoMock
            )
            ->queue();
    }
}
