<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Model\Checkout\Orderline;

use Klarna\Kp\Plugin\Model\Checkout\Orderline\CollectorPlugin;
use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Klarna\Core\Model\Checkout\Orderline\Collector;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\Checkout\Orderline\CollectorPlugin
 */
class CollectorPluginTest extends TestCase
{
    /**
     * @var CollectorPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var Collector|MockObject
     */
    private $subject;

    /**
     * @covers ::afterIsKlarnaActive()
     */
    public function testAfterIsKlarnaActiveResultInputIsNotNull(): void
    {
        $result = $this->model->afterIsKlarnaActive($this->subject, true, null);
        static::assertTrue($result);
    }

    /**
     * @covers ::afterIsKlarnaActive()
     */
    public function testAfterIsKlarnaActiveResultInputIsNull(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(false);
        $result = $this->model->afterIsKlarnaActive($this->subject, null, null);
        static::assertFalse($result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->model = $objectFactory->create(CollectorPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

       $this->subject = $mockFactory->create(Collector::class);
    }
}
