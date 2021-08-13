<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Model\System;

use Klarna\Core\Model\System\Reference;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Data\Form\Element\Text;

/**
 * @coversDefaultClass \Klarna\Core\Model\System\Reference
 */
class ReferenceTest extends TestCase
{
    /**
     * @var Reference
     */
    private $model;
    /**
     * @var MockFactory
     */
    private $mockFactory;

    /**
     * @covers ::render()
     */
    public function testRenderReturnResult(): void
    {
        $element = $this->mockFactory->create(Text::class);
        static::assertIsString($this->model->render($element));
    }

    protected function setUp(): void
    {
        $this->mockFactory   = new MockFactory();
        $objectFactory = new TestObjectFactory($this->mockFactory);
        $this->model  = $objectFactory->create(Reference::class);
    }
}