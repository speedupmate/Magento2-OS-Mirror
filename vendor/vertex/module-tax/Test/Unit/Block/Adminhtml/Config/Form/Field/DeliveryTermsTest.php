<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\DeliveryTerms;
use Vertex\Tax\Model\Config\Source\DeliveryTerm;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test Class @see DeliveryTerms
 */
class DeliveryTermsTest extends TestCase
{
    /** @var MockObject|DeliveryTerms */
    private $blockMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void  // @codingStandardsIgnoreLine MEQP2.PHP.ProtectedClassMember.FoundProtected
    {
        parent::setUp();
        $escaper = $this->getObject(Escaper::class);
        $context = $this->getObject(
            Context::class,
            [
                'escaper' => $escaper,
            ]
        );
        $delivery = $this->getObject(DeliveryTerm::class);
        $this->blockMock = $this->getObject(
            DeliveryTerms::class,
            [
                'deliveryTerms' => $delivery,
                'context' => $context,
            ]
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
