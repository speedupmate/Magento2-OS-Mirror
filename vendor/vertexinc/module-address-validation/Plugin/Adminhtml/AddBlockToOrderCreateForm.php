<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Plugin\Adminhtml;

use Magento\Framework\Data\Form;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Address;
use Vertex\AddressValidation\Block\Adminhtml\CleanseAddressButton;

/**
 * @see Address
 */
class AddBlockToOrderCreateForm
{
    private const DEFAULT_FORM_ID = 'edit_form';

    private const ELEMENT_NAME = 'address_validation_type';

    /** @var LayoutInterface */
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @see Address::getForm()
     */
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

        if (!$subject->getData('js_variable_prefix')) {
            return $result;
        }

        /** @var CleanseAddressButton $block */
        $block = $this->layout->createBlock(
            CleanseAddressButton::class,
            '',
            ['prefix' => $subject->getData('js_variable_prefix')]
        );

        $validationElement->setRenderer($block);

        return $result;
    }
}
