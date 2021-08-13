<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Test\Unit\Cron;

use Magento\Framework\Exception\CouldNotDeleteException;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\RequestLogging\Cron\LogRotate;
use Vertex\RequestLogging\Model\Config;
use Vertex\RequestLogging\Model\LogEntryRotator;
use Vertex\RequestLogging\Model\LogEntryRotatorFactory;
use Vertex\RequestLogging\Test\Unit\TestCase;

/**
 * Test that LogRotate may be run under expected conditions.
 */
class LogRotateTest extends TestCase
{
    /** @var MockObject|Config */
    private $configMock;

    /** @var LogRotate */
    private $logRotate;

    /** @var MockObject|LogEntryRotatorFactory */
    private $logEntryRotatorFactoryMock;

    /**
     * Perform test setup.
     */
    protected function setUp(): void
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
     * @covers LogRotate::execute()
     * @throws CouldNotDeleteException
     */
    public function testRunRotateWhenEnabled(): void
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
     * @covers LogRotate::execute()
     * @throws CouldNotDeleteException
     */
    public function testSkipRotateWhenDisabled(): void
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
