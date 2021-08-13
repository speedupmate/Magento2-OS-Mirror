<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Core\Test\Unit\Logger\Api;

use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Logger\Api\Container;
use Klarna\Core\Logger\Api\Logger;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Logger\Api\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var Container
     */
    private $container;

    /**
     * @doesNotPerformAssertions
     * @covers ::logContainer
     */
    public function testLogContainerFlagIsSetToFalse(): void
    {
        $this->dependencyMocks['loggerUpdate']->expects(static::never())
            ->method('addEntry');
        $this->logger->logContainer($this->container);
    }

    /**
     * @doesNotPerformAssertions
     * @covers ::logContainer
     */
    public function testLogContainerAddEntry(): void
    {
        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(true);
        $this->logger->logContainer($this->container);
    }

    protected function setUp(): void
    {
        $mockFactory     = new MockFactory();
        $objectFactory   = new TestObjectFactory($mockFactory);
        $this->logger    = $objectFactory->create(Logger::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->container = $mockFactory->create(Container::class);
    }
}
