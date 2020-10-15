<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Plugin\Adminhtml;

use Magento\Framework\Data\Form;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm;
use Vertex\AddressValidation\Block\Adminhtml\CleanseAddressButton;

class AddBlockToOrderCreateForm
{
    private const ELEMENT_NAME = 'address_validation_type';

    /** @var LayoutInterface */
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    public function afterGetForm(AbstractForm $subject, Form $result): Form
    {
        $validationElement = $result->getElement(static::ELEMENT_NAME);
        if (!$validationElement) {
            $validationElement = $result->addField(
                static::ELEMENT_NAME,
                'hidden',
                ['name' => static::ELEMENT_NAME]
            );
        }

        /** @var CleanseAddressButton $block */
        $block = $this->layout->createBlock(
            CleanseAddressButton::class,
            '',
            ['prefix' => $subject->getJsVariablePrefix()]
        );

        $validationElement->setRenderer($block);

        return $result;
    }
}
