<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Test\Unit\Model\Api\Rest\Service;

use Klarna\Core\Api\VersionInterface;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Api\Data\RequestInterface;
use Klarna\Kp\Model\Api\Response;
use Klarna\Kp\Model\Api\Rest\Service\Payments;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass  \Klarna\Kp\Model\Api\Rest\Service\Payments
 */
class PaymentsTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Payments
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::createSession()
     * @doesNotPerformAssertions
     */
    public function testCreateSessionIsUserAgentSetCorrectly(): void
    {
        $versionInterface = $this->createMock(VersionInterface::class);

        $this->dependencyMocks['klarnaConfig']
            ->method('getVersionConfig')
            ->willReturn($versionInterface);

        $this->dependencyMocks['versionInfo']
            ->method('getVersion')
            ->willReturn('');

        $this->dependencyMocks['versionInfo']
            ->method('getModuleVersionString')
            ->willReturn('module_version_string');

        $this->dependencyMocks['versionInfo']
            ->method('getMageMode')
            ->willReturn('mage_mode');

        $this->dependencyMocks['versionInfo']
            ->method('getMageEdition')
            ->willReturn('mage_edition');

        $this->dependencyMocks['versionInfo']
            ->method('getMageVersion')
            ->willReturn('mage_version');

        $this->dependencyMocks['service']
            ->method('makeRequest')
            ->willReturn([
                'response_status_code' => 123
            ]);

        $response = $this->createMock(Response::class);
        $this->dependencyMocks['responseFactory']
            ->method('create')
            ->willReturn($response);

        $request = $this->mockFactory->createMock(RequestInterface::class);
        $request->method('toArray')->willReturn([]);

        $this->dependencyMocks['service']
            ->method('setUserAgent')
            ->with(
                'Magento2_KP',
                'module_version_string',
                'Magento mage_edition/mage_version mage_mode mode'
            );

        $this->model->createSession($request);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);

        $this->model = $objectFactory->create(Payments::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
