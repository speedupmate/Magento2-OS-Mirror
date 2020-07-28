<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Model;

use Klarna\Kp\Model\SessionInitiatorFactory;
use Klarna\Kp\Plugin\Model\MethodListPlugin;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Payment\Model\MethodList;
use Klarna\Kp\Model\SessionInitiator;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\MethodListPlugin
 */
class MethodListPluginTest extends TestCase
{
    /**
     * @var MethodListPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var MethodList|MockObject
     */
    private $subject;
    /**
     * @var Quote|MockObject
     */
    private $quote;
    /**
     * @var MockFactory
     */
    private $mockFactory;

    /**
     * @covers ::beforeGetAvailableMethods()
     */
    public function testBeforeGetAvailableMethodsQuoteInstanceIsNull(): void
    {
        $result = $this->model->beforeGetAvailableMethods($this->subject, null);
        static::assertEquals([null], $result);
    }

    /**
     * @covers ::beforeGetAvailableMethods()
     */
    public function testBeforeGetAvailableMethodsPaymentIsDisabled(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(false);

        $result = $this->model->beforeGetAvailableMethods($this->subject, $this->quote);
        static::assertEquals([$this->quote], $result);
    }

    /**
     * @covers ::beforeGetAvailableMethods()
     */
    public function testBeforeGetAvailableMethodsPaymentIsEnabled(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(true);

        $sessionInitiator = $this->mockFactory->create(SessionInitiator::class);
        $this->dependencyMocks['sessInitFactory']->method('create')
            ->willReturn($sessionInitiator);

        $result = $this->model->beforeGetAvailableMethods($this->subject, $this->quote);
        static::assertEquals([$this->quote], $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);

        $this->model = $objectFactory->create(MethodListPlugin::class, [
            SessionInitiatorFactory::class => ['create']
        ]);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->subject = $this->mockFactory->create(MethodList::class);
        $this->quote = $this->mockFactory->create(Quote::class);
    }
}
