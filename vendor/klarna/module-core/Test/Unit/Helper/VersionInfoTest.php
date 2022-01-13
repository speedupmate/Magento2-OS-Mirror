<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Helper;

use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass  \Klarna\Core\Helper\VersionInfo
 */
class VersionInfoTest extends TestCase
{
    /**
     * @var VersionInfo
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getModuleVersionString()
     */
    public function testGetModuleVersionString(): void
    {
        $this->dependencyMocks['resource']
            ->method('getDataVersion')
            ->willReturn('x.y.z');

        $result = $this->model->getModuleVersionString('a.b.c', '');
        static::assertSame('a.b.c;Core/x.y.z', $result);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->model = $objectFactory->create(VersionInfo::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
