<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Controller\Api;

use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Plugin\Controller\Api\NotificationPlugin;
use Klarna\Ordermanagement\Controller\Api\Notification;
use Magento\Sales\Model\Order;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Controller\Api\NotificationPlugin
 */
class NotificationPluginTest extends TestCase
{
    /**
     * @var NotificationPlugin
     */
    private $notificationPlugin;
    /**
     * @var Notification|MockObject
     */
    private $notification;
    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * Passing 'klarna_kp' as method, returning method 'klarna_kp'.
     *
     * @covers ::beforeSetOrderStatus
     */
    public function testBeforeSetOrderStatusForKpMethod(): void
    {
        $expected = 'klarna_kp';
        $actual   = $this->notificationPlugin->beforeSetOrderStatus($this->notification, $this->order, $expected, '')[1];
        static::assertEquals($expected, $actual);
    }

    /**
     * Passing 'klarna_kco' as method, returning method 'klarna_kco'.
     *
     * @covers ::beforeSetOrderStatus
     */
    public function testBeforeSetOrderStatusForKcoMethod(): void
    {
        $expected = 'klarna_kco';
        $actual   = $this->notificationPlugin->beforeSetOrderStatus($this->notification, $this->order, $expected, '')[1];
        static::assertEquals($expected, $actual);
    }

    /**
     * Passing '' as method, returning method 'klarna_kp'.
     *
     * @covers ::beforeSetOrderStatus
     */
    public function testBeforeSetOrderStatusForUndefinedMethod(): void
    {
        $actual = $this->notificationPlugin->beforeSetOrderStatus($this->notification, $this->order, '', '')[1];
        static::assertEquals('klarna_kp', $actual);
    }

    /**
     * Passing 'klarna_kp' as method, returning array of 3 elements.
     *
     * @covers ::beforeSetOrderStatus
     */
    public function testBeforeSetOrderStatusForKpMethodCheckReturnValue(): void
    {
        $actual = $this->notificationPlugin->beforeSetOrderStatus($this->notification, $this->order, 'klarna_kp', '');
        static::assertCount(3, $actual);
    }

    /**
     * Basic setup for test
     */
    protected function setUp()
    {
        $mockFactory              = new MockFactory();
        $objectFactory            = new TestObjectFactory($mockFactory);
        $this->notificationPlugin = $objectFactory->create(NotificationPlugin::class);
        $this->notification       = $mockFactory->create(Notification::class);
        $this->order              = $mockFactory->create(Order::class);
    }
}