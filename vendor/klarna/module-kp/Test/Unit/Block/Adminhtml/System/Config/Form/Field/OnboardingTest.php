<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Kp\Tests\Unit\Block;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Block\Adminhtml\System\Config\Form\Field\Onboarding;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @coversDefaultClass Klarna\Kp\Block\Adminhtml\System\Config\Form\Field\Onboarding
 */
class OnboardingTest extends TestCase
{
    /**
     * @var Onboarding
     */
    private $onboarding;
    /**
     * @var AbstractElement|MockObject
     */
    private $abstractElement;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * No onboarding URL is set, return rendered element markup.
     *
     * @covers ::render
     */
    public function testRenderWithoutUrl(): void
    {
        $expected = '<tr id="row_"><td class="label"><label for=""><span></span></label></td><td class="value"></td><td class=""></td></tr>';

        $this->dependencyMocks['onboarding']->method('getUrl')->willReturn('');

        $actual = $this->onboarding->render($this->abstractElement);
        static::assertEquals($expected, $actual);
    }

    /**
     * If onboarding URL is set, return rendered onboarding link markup.
     *
     * @covers ::render
     */
    public function testRenderWithUrl(): void
    {
        $expected = 'Click on this %1 to visit the Klarna Merchant Onboarding Page and request credentials.';

        $this->dependencyMocks['onboarding']->method('getUrl')->willReturn('http://bla.de');

        $actual = $this->onboarding->render($this->abstractElement)->getText();
        static::assertEquals($expected, $actual);
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->onboarding      = $objectFactory->create(Onboarding::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
        $this->abstractElement = $mockFactory->create(AbstractElement::class);
    }
}