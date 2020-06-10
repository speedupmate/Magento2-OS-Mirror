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

use Klarna\Core\Api\OrderInterface;

/**
 * @coversDefaultClass \Klarna\Core\Model\Order
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mageOrderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var Order
     */
    protected $model;

    /**
     * @covers ::getIsAcknowledged()
     * @covers ::setIsAcknowledged()
     * @covers ::isAcknowledged()
     */
    public function testIsAcknowledgedAccessors()
    {
        $value = 1;

        $result = $this->model->setIsAcknowledged($value)->getIsAcknowledged();
        $this->assertEquals($value, $result);
        $this->assertEquals($value, $this->model->isAcknowledged());
    }

    /**
     * @covers ::getKlarnaOrderId()
     * @covers ::setKlarnaOrderId()
     */
    public function testKlarnaOrderIdAccessors()
    {
        $value = 'KLARNA-ORDER-ID';

        $result = $this->model->setKlarnaOrderId($value)->getKlarnaOrderId();
        $this->assertEquals($value, $result);
    }

    /**
     * @covers ::getOrderId()
     * @covers ::setOrderId()
     */
    public function testOrderIdAccessors()
    {
        $value = 1;

        $result = $this->model->setOrderId($value)->getOrderId();
        $this->assertEquals($value, $result);
    }

    /**
     * @covers ::getIdentities()
     */
    public function testGetIdentities()
    {
        $value = 1;

        $this->model->setId($value);
        $result = $this->model->getIdentities();
        $this->assertEquals([\Klarna\Core\Model\Order::CACHE_TAG . '_' . $value], $result);
    }

    /**
     * @covers ::getReservationId()
     * @covers ::setReservationId()
     */
    public function testReservationIdAccessors()
    {
        $value = 'RESERVATION-ID';

        $result = $this->model->setReservationId($value)->getReservationId();
        $this->assertEquals($value, $result);
    }

    /**
     * @covers ::getSessionId()
     * @covers ::setSessionId()
     */
    public function testSessionIdAccessors()
    {
        $value = 'klarna-session-id';

        $result = $this->model->setSessionId($value)->getSessionId();
        $this->assertEquals($value, $result);
    }

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
                                        ->setMethods(['create'])
                                        ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Klarna\Core\Model\ResourceModel\Order::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->mageOrderMock = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);

        $this->model = $objectManager->getObject(\Klarna\Core\Model\Order::class);
    }
}
