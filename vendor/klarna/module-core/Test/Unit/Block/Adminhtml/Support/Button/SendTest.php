<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Block\Adminhtml\Support\Button;

use Klarna\Core\Block\Adminhtml\Support\Button\Send;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Block\Adminhtml\Support\Button\Send
 */
class SendTest extends TestCase
{
    /**
     * @var Send
     */
    private $send;

    /**
     * @covers ::getButtonData()
     */
    public function testGetButtonDataReturnsArray(): void
    {
        static::assertIsArray($this->send->getButtonData());
    }

    protected function setUp(): void
    {
        $mockFactory = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);
        $this->send = $objectFactory->create(Send::class);
    }
}