<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Model\Queue;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterfaceFactory;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\OrderItemNotificationEnqueuer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class OrderNotificationEnqueuerTest extends TestCase
{
    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderInterfaceMock;

    /**
     * @var SmsOrderInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderInterfaceFactoryMock;

    /**
     * @var TransactionalSms|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionalSmsMock;

    /**
     * @var SmsOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderRepositoryInterfaceMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerInterfaceMock;

    /**
     * @var OrderItemNotificationEnqueuer
     */
    private $smsOrderNotificationEnqueuer;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeInterfaceMock;

    /**
     * @var WebsiteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteInterfaceMock;

    /**
     * @var SmsOrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $smsOrderInterfaceMock;

    protected function setUp(): void
    {
        $assignedMethods = ['getId','getData','getShippingAddress', 'getTelephone'];
        $this->orderInterfaceMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(OrderInterface::class), $assignedMethods))
            ->getMock();

        $this->smsOrderInterfaceFactoryMock = $this->createMock(SmsOrderInterfaceFactory::class);
        $this->transactionalSmsMock = $this->createMock(TransactionalSms::class);
        $this->smsOrderRepositoryInterfaceMock = $this->createMock(SmsOrderRepositoryInterface::class);
        $this->storeManagerInterfaceMock = $this->createMock(StoreManagerInterface::class);
        $this->storeInterfaceMock = $this->createMock(StoreInterface::class);
        $this->websiteInterfaceMock = $this->createMock(WebsiteInterface::class);
        $this->smsOrderInterfaceMock = $this->createMock(SmsOrderInterface::class);

        $this->smsOrderNotificationEnqueuer = new OrderItemNotificationEnqueuer(
            $this->smsOrderInterfaceFactoryMock,
            $this->smsOrderRepositoryInterfaceMock,
            $this->transactionalSmsMock,
            $this->storeManagerInterfaceMock
        );
    }

    public function testThatSmsWillBeStoredIfSmsIsEnabled()
    {
        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);

        $this->storeInterfaceMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($storeId = 1);

        $this->transactionalSmsMock->expects($this->once())
            ->method('isSmsEnabled')
            ->with($storeId)
            ->willReturn(true);

        $this->transactionalSmsMock->expects($this->once())
            ->method('isSmsTypeEnabled')
            ->willReturn(true);

        $this->orderInterfaceMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->orderInterfaceMock);

        $this->orderInterfaceMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->orderInterfaceMock);

        $this->orderInterfaceMock->expects($this->once())
            ->method('getTelephone')
            ->willReturn($telephone = '+4407400000000');

        $this->orderInterfaceMock->expects($this->once())
            ->method('getCustomerEmail')
            ->willReturn($email = 'chaz@emailsim.io');

        $this->orderInterfaceMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId = 1);

        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteInterfaceMock);

        $this->websiteInterfaceMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId = 1);

        $this->smsOrderInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId)
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId)
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setTypeId')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setStatus')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setPhoneNumber')
            ->with($telephone)
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setAdditionalData')
            ->with('{order_status:pending}')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderInterfaceMock->expects($this->once())
            ->method('setAdditionalData')
            ->with('{order_status:pending}')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderRepositoryInterfaceMock
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->smsOrderInterfaceMock);

        $this->smsOrderNotificationEnqueuer->queue(
            $this->orderInterfaceMock,
            '{order_status:pending}',
            'chazPath',
            'chazType'
        );
    }

    public function testThatSmsWillNotBeStoredIfSmsIsNotEnabled()
    {
        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);

        $this->storeInterfaceMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId = 1);

        $this->transactionalSmsMock->expects($this->once())
            ->method('isSmsEnabled')
            ->with($storeId)
            ->willReturn(false);

        $this->transactionalSmsMock->expects($this->never())
            ->method('isSmsTypeEnabled');

        $this->orderInterfaceMock->expects($this->never())
            ->method('getShippingAddress');

        $this->orderInterfaceMock->expects($this->never())
            ->method('getData');

        $this->orderInterfaceMock->expects($this->never())
            ->method('getId');

        $this->storeManagerInterfaceMock->expects($this->never())
            ->method('getWebsite');

        $this->websiteInterfaceMock
            ->expects($this->never())
            ->method('getId');

        $this->smsOrderInterfaceFactoryMock->expects($this->never())
            ->method('create');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setOrderId');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setStoreId');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setWebsiteId');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setTypeId');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setStatus');

        $this->smsOrderInterfaceMock->expects($this->never())
            ->method('setPhoneNumber');

        $this->smsOrderRepositoryInterfaceMock->expects($this->never())
            ->method('save');

        $this->smsOrderNotificationEnqueuer->queue(
            $this->orderInterfaceMock,
            '',
            'chazPath',
            'chazType'
        );
    }
}
