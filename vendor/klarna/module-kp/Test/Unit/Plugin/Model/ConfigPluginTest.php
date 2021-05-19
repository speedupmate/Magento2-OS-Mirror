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

use Klarna\Kp\Plugin\Model\ConfigPlugin;
use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Klarna\Core\Model\Config;

/**
 * @coversDefaultClass \Klarna\Kp\Plugin\Model\ConfigPlugin
 */
class ConfigPluginTest extends TestCase
{
    /**
     * @var ConfigPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var Config|MockObject
     */
    private $subject;

    /**
     * @covers ::afterKlarnaEnabled()
     */
    public function testAfterKlarnaEnabledResultInputIsNotNull(): void
    {
        $result = $this->model->afterKlarnaEnabled($this->subject, true, null);
        static::assertTrue($result);
    }

    /**
     * @covers ::afterKlarnaEnabled()
     */
    public function testAfterKlarnaEnabledResultInputIsNull(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(false);
        $result = $this->model->afterKlarnaEnabled($this->subject, null, null);
        static::assertFalse($result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->model = $objectFactory->create(ConfigPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->subject = $mockFactory->create(Config::class);
    }
}
