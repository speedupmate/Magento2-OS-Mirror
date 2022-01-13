<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\KpGraphQl\Test\Unit\Plugin\Model\Api\Rest;

use Klarna\Core\Model\Api\Rest\Service;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\KpGraphQl\Plugin\Model\Api\Rest\ServicePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\KpGraphQl\Plugin\Model\Api\Rest\ServicePlugin
 */
class ServicePluginTest extends TestCase
{
    /**
     * @var ServicePlugin
     */
    private $servicePlugin;
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::beforeSetUserAgent
     */
    public function testBeforeSetUserAgentParameterNotSetResultsInUnchangedVersion(): void
    {
        $this
            ->dependencyMocks['request']
            ->method('getParam')
            ->with('GraphQlCreateSession')
            ->willReturn(null);

        $resultArray = $this->servicePlugin->beforeSetUserAgent(
            $this->mockFactory->create(Service::class),
            'product',
            'version',
            'mageInfo'
        );

        self::assertSame('version', $resultArray[1]);
    }

    /**
     * @covers ::beforeSetUserAgent
     */
    public function testBeforeSetUserAgentParameterSetResultsInChangedVersion(): void
    {
        $this
            ->dependencyMocks['request']
            ->method('getParam')
            ->with('GraphQlCreateSession')
            ->willReturn(true);

        $resultArray = $this->servicePlugin->beforeSetUserAgent(
            $this->mockFactory->create(Service::class),
            'product',
            'version',
            'mageInfo'
        );

        self::assertSame('version;GraphQlCreateSession', $resultArray[1]);
    }

    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);
        $this->servicePlugin = $objectFactory->create(ServicePlugin::class);

        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
