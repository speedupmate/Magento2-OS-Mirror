<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Checkout\Block;

use Klarna\Kp\Plugin\Checkout\Block\OnepagePlugin;
use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Checkout\Block\Onepage;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Checkout\Block\OnepagePlugin
 */
class OnepagePluginTest extends TestCase
{
    /**
     * @var OnepagePlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var OnePage|MockObject
     */
    private $subject;

    /**
     * @covers ::beforeGetJsLayout()
     */
    public function testBeforeGetJsLayoutFlagIsNotSet(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(false);
        $this->dependencyMocks['kpSession']->expects(static::never())
            ->method('init');

        $result = $this->model->beforeGetJsLayout($this->subject);
        static::assertEquals([], $result);
    }

    /**
     * @covers ::beforeGetJsLayout()
     */
    public function testBeforeGetJsLayoutFlagIsSet(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(true);

        $result = $this->model->beforeGetJsLayout($this->subject);
        static::assertEquals([], $result);
    }

    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->model = $objectFactory->create(OnepagePlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->subject = $mockFactory->create(OnePage::class);
    }
}
