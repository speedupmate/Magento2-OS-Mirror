<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\Countries;
use Vertex\Tax\Model\Config\Source\Country;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test Class @see Countries
 */
class CountriesTest extends TestCase
{
    /** @var MockObject|Countries */
    private $blockMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void // @codingStandardsIgnoreLine MEQP2.PHP.ProtectedClassMember.FoundProtected
    {
        parent::setUp();
        $escaper = $this->getObject(Escaper::class);
        $context = $this->getObject(
            Context::class,
            [
                'escaper' => $escaper,
            ]
        );
        $countryMock = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->getMock();
        $countryMock->method('toOptionArray')->willReturn(
            [
                [
                    'value' => 'PT',
                    'label' => 'Portugal',
                    'is_region_visible' => false,
                ],
            ]
        );
        $this->blockMock = $this->getObject(
            Countries::class,
            [
                'context' => $context,
                'country' => $countryMock,
            ]
        );
    }

    /**
     * Test country options
     *
     * @return void
     */
    public function testGetOptions()
    {
        $this->assertEquals(
            [
                [
                    'value' => 'PT',
                    'label' => 'Portugal',
                    'is_region_visible' => false,
                ],
            ],
            $this->blockMock->getOptions()
        );
    }

    /**
     * Test setting Input name
     *
     * @return void
     */
    public function testSetInputName()
    {
        $this->blockMock->setInputName('Test');
        $this->assertEquals('Test', $this->blockMock->getName());
    }
}
