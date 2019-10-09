<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Cron;

use Vertex\Tax\Cron\LogRotate;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\LogEntryRotator;
use Vertex\Tax\Model\LogEntryRotatorFactory;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test that LogRotate may be run under expected conditions.
 */
class LogRotateTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Config */
    private $configMock;

    /** @var LogRotate */
    private $logRotate;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LogEntryRotatorFactory */
    private $logEntryRotatorFactoryMock;

    /**
     * Perform test setup.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->configMock = $this->createMock(Config::class);
        $this->logEntryRotatorFactoryMock = $this->createMock(LogEntryRotatorFactory::class);

        $this->logRotate = $this->getObject(
            LogRotate::class,
            [
                'logEntryRotatorFactory' => $this->logEntryRotatorFactoryMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * Test that rotation may proceed when the feature is enabled.
     *
     * @covers \Vertex\Tax\Cron\LogRotate::execute()
     */
    public function testRunRotateWhenEnabled()
    {
        $this->configMock->method('isLogRotationEnabled')
            ->willReturn(true);

        $this->logEntryRotatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->createMock(LogEntryRotator::class)
            );

        $this->logRotate->execute();
    }

    /**
     * Test that rotating does not run when the feature is disabled.
     *
     * @covers \Vertex\Tax\Cron\LogRotate::execute()
     */
    public function testSkipRotateWhenDisabled()
    {
        $this->configMock->method('isLogRotationEnabled')
            ->willReturn(false);

        $this->logEntryRotatorFactoryMock->expects($this->never())
            ->method('create')
            ->willReturn(
                $this->createMock(LogEntryRotator::class)
            );

        $this->logRotate->execute();
    }
}
