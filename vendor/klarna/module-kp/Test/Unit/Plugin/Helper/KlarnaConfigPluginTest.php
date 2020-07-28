<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Tests\Unit\Plugin\Helper;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Plugin\Helper\KlarnaConfigPlugin;
use Klarna\Core\Helper\KlarnaConfig;
use Klarna\Core\Api\VersionInterface;
use Klarna\Kp\Model\Api\Builder\Kasper;

/**
 * @coversDefaultClass Klarna\Kp\Plugin\Helper\KlarnaConfigPlugin
 */
class KlarnaConfigPluginTest extends TestCase
{
    /**
     * @var KlarnaConfigPlugin
     */
    private $klarnaConfigPlugin;
    /**
     * @var KlarnaConfig|MockObject
     */
    private $klarnaConfig;
    /**
     * @var VersionInterface|MockObject
     */
    private $versionInterface;

    /**
     * Passing 'klarna_kp' as method, returning kasper class name.
     *
     * @covers ::afterGetOmBuilderType
     */
    public function testAfterGetOmBuilderTypeForKpMethod(): void
    {
        $actual = $this->klarnaConfigPlugin->afterGetOmBuilderType($this->klarnaConfig, '', $this->versionInterface, 'klarna_kp');
        static::assertEquals(Kasper::class, $actual);
    }

    /**
     * Passing 'klarna_kco' as method, returning the second argument.
     *
     * @covers ::afterGetOmBuilderType
     */
    public function testAfterGetOmBuilderTypeForKcoMethod(): void
    {
        $actual = $this->klarnaConfigPlugin->afterGetOmBuilderType($this->klarnaConfig, '', $this->versionInterface, 'klarna_kco');
        static::assertEquals('', $actual);
    }

    /**
     * Passing empty string as method, returning kasper class name.
     *
     * @covers ::afterGetOmBuilderType
     */
    public function testAfterGetOmBuilderTypeForUndefinedMethod(): void
    {
        $actual = $this->klarnaConfigPlugin->afterGetOmBuilderType($this->klarnaConfig, '', $this->versionInterface, '');
        static::assertEquals(Kasper::class, $actual);
    }

    /**
     * Passing no method at all, returning kasper class name.
     *
     * @covers ::afterGetOmBuilderType
     */
    public function testAfterGetOmBuilderTypeForMissingMethod(): void
    {
        $actual = $this->klarnaConfigPlugin->afterGetOmBuilderType($this->klarnaConfig, '', $this->versionInterface);
        static::assertEquals(Kasper::class, $actual);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory              = new MockFactory();
        $objectFactory            = new TestObjectFactory($mockFactory);
        $this->klarnaConfigPlugin = $objectFactory->create(KlarnaConfigPlugin::class);
        $this->klarnaConfig       = $mockFactory->create(KlarnaConfig::class);
        $this->versionInterface   = $mockFactory->create(VersionInterface::class);
    }
}
