<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model;

use Klarna\Core\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @coversDefaultClass \Klarna\Core\Model\OrderRepository
 */
class OrderRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderRepositoryInterface|\Klarna\Core\Model\OrderRepository
     */
    protected $model;

    /**
     * @var OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Klarna\Core\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Klarna\Core\Model\ResourceModel\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mageOrderMock;

    /**
     * @var \Klarna\Core\Model\ResourceModel\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @covers ::getById()
     */
    public function testGetByIdWithException()
    {
        $this->expectException('\Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('Order with id "" does not exist');
        $orderId = '';
        $this->orderFactoryMock->expects(static::once())->method('create')->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::once())->method('getId')->willReturn(false);

        $this->model->getById($orderId);
    }

    /**
     * @covers ::getByOrder()
     */
    public function testGetByOrder()
    {
        $orderId = 15;

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByOrder')
            ->with($this->mageOrderMock)
            ->willReturn($orderId);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);

        static::assertEquals($this->orderMock, $this->model->getByOrder($this->mageOrderMock));
    }

    /**
     * @covers ::getByOrder()
     */
    public function testGetByOrderWithException()
    {
        $this->expectException('\Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('Requested order doesn\'t exist');
        $orderId = '';
        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByOrder')
            ->with($this->mageOrderMock)
            ->willReturn($orderId);

        static::assertEquals($this->orderMock, $this->model->getByOrder($this->mageOrderMock));
    }

    /**
     * @covers ::getByReservationId()
     */
    public function testGetByReservationId()
    {
        $orderId = 15;
        $reservationId = 'RESERVATION-ID';

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByReservationId')
            ->with($reservationId)
            ->willReturn($orderId);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);

        static::assertEquals($this->orderMock, $this->model->getByReservationId($reservationId));
    }

    /**
     * @covers ::getByReservationId()
     */
    public function testGetByReservationIdWithException()
    {
        $this->expectException('\Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('Order with Reservation ID "" does not exist.');
        $orderId = '';
        $reservationId = '';
        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByReservationId')
            ->with($reservationId)
            ->willReturn($orderId);

        static::assertEquals($this->orderMock, $this->model->getByReservationId($reservationId));
    }

    /**
     * @covers ::getBySessionId()
     */
    public function testGetBySessionId()
    {
        $orderId = 15;
        $sessionId = 'SESSION-ID';

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdBySessionId')
            ->with($sessionId)
            ->willReturn($orderId);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);

        static::assertEquals($this->orderMock, $this->model->getBySessionId($sessionId));
    }

    /**
     * @covers ::getBySessionId()
     */
    public function testGetBySessionIdWithException()
    {
        $this->expectException('\Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('Order with session_id "" does not exist.');
        $orderId = '';
        $sessionId = '';
        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdBySessionId')
            ->with($sessionId)
            ->willReturn($orderId);

        static::assertEquals($this->orderMock, $this->model->getBySessionId($sessionId));
    }

    /**
     * @covers ::getById()
     */
    public function testGetById()
    {
        $orderId = 15;

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::once())
            ->method('getId')
            ->willReturn($orderId);

        static::assertEquals($this->orderMock, $this->model->getById($orderId));
    }

    /**
     * @covers ::getByKlarnaOrderId()
     */
    public function testGetByKlarnaOrderId()
    {
        $orderId = 15;
        $klarnaOrderId = 'KLARNA-ORDER-ID';

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByKlarnaOrderId')
            ->with($klarnaOrderId)
            ->willReturn($orderId);
        $this->orderResourceMock->expects(static::once())
            ->method('load')
            ->with($this->orderMock, $orderId)
            ->willReturn($this->orderMock);

        static::assertEquals($this->orderMock, $this->model->getByKlarnaOrderId($klarnaOrderId));
    }

    /**
     * @covers ::getByKlarnaOrderId()
     */
    public function testGetByKlarnaOrderIdNotExists()
    {
        $orderId = '';
        $klarnaOrderId = 'KLARNA-ORDER-ID';

        $this->orderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderResourceMock->expects(static::once())
            ->method('getIdByKlarnaOrderId')
            ->with($klarnaOrderId)
            ->willReturn($orderId);
        $this->orderMock->expects(static::once())
            ->method('setKlarnaOrderId')
            ->with($klarnaOrderId);

        static::assertEquals($this->orderMock, $this->model->getByKlarnaOrderId($klarnaOrderId));
    }

    /**
     * @covers ::save()
     */
    public function testSave()
    {
        $this->orderResourceMock->expects(static::once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);

        $this->model->save($this->orderMock);
    }

    /**
     * @covers ::save()
     */
    public function testSaveWithException()
    {
        $exceptionMessage = "No such entity with payments_quote_id = ''";
        $this->expectException('\Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage($exceptionMessage);
        $this->orderResourceMock->expects(static::once())
            ->method('save')
            ->with($this->orderMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->model->save($this->orderMock);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->mageOrderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            [
                'load',
                'getId',
                '__wakeup',
                'save',
                'delete',
                'getCustomerId',
                'getStoreId',
                'getData'
            ]
        );
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->orderResourceMock = $this->getMockBuilder(\Klarna\Core\Model\ResourceModel\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save',
                    'load',
                    'getIdByKlarnaOrderId',
                    'getIdByOrder',
                    'getIdByReservationId',
                    'getIdBySessionId'
                ]
            )
            ->getMock();

        $this->orderFactoryMock = $this->getMockBuilder(\Klarna\Core\Model\OrderFactory::class)->setMethods(['create'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Klarna\Core\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                'getId',
                '__wakeup',
                'save',
                'delete',
                'setKlarnaOrderId'
            ])
            ->getMock();

        $this->model = $objectManager->getObject(
            \Klarna\Core\Model\OrderRepository::class,
            [
                'orderFactory'  => $this->orderFactoryMock,
                'resourceModel' => $this->orderResourceMock
            ]
        );
    }
}
