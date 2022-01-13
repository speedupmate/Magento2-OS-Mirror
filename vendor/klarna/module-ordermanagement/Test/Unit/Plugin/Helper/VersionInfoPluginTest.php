<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Ordermanagement\Test\Unit\Plugin\Helper;

use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Ordermanagement\Plugin\Helper\VersionInfoPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass  \Klarna\Ordermanagement\Plugin\Helper\VersionInfoPlugin
 */
class VersionInfoPluginTest extends TestCase
{
    /**
     * @var VersionInfoPlugin
     */
    private $model;

    /**
     * @covers ::afterGetModuleVersionString()
     */
    public function testAfterGetModuleVersionStringAppendsVersion(): void
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo
            ->method('getVersion')
            ->willReturn('x.y.z');

        $result = $this->model->afterGetModuleVersionString(
            $versionInfo,
            'before',
            '',
            'Not_Klarna_Ordermanagement'
        );

        self::assertSame('before;OM/x.y.z', $result);
    }

    /**
     * @covers ::afterGetModuleVersionString()
     */
    public function testAfterGetModuleVersionStringDoesNotAppendVersion(): void
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo
            ->method('getVersion')
            ->willReturn('x.y.z');

        $result = $this->model->afterGetModuleVersionString(
            $versionInfo,
            'before',
            '',
            'Klarna_Ordermanagement'
        );

        self::assertSame('before', $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->model = $objectFactory->create(VersionInfoPlugin::class);
    }
}
