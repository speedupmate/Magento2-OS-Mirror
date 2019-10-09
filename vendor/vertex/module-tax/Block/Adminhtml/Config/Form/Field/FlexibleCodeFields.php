<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FieldSource\OptionProvider;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FieldSource\OptionProviderInterface;

/**
 * Represents a table of flexible code fields in the admin configuration
 */
class FlexibleCodeFields extends AbstractFieldArray implements OptionProviderInterface
{
    /** @var FlexibleFieldId */
    private $fieldIdRenderer;

    /** @var FlexibleFieldSource */
    private $fieldSourceRenderer;

    /** @var OptionProvider */
    private $optionProvider;

    /** @var FlexibleFieldUtilities */
    private $utilities;

    /**
     * @param Context $context
     * @param FlexibleFieldUtilities $utilities
     * @param OptionProvider $optionProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        FlexibleFieldUtilities $utilities,
        OptionProvider $optionProvider,
        array $data = []
    ) {
        $this->utilities = $utilities;
        $this->optionProvider = $optionProvider;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->optionProvider->getOptions($this->getFieldSourceRenderer()->getOptions());
    }

    /** @inheritDoc */
    protected function _prepareArrayRow(DataObject $row)
    {
        $this->utilities->prepareArrayRow($row, $this->getFieldSourceRenderer());
    }

    /** @inheritDoc */
    protected function _prepareToRender()
    {
        parent::_prepareToRender();
        $this->utilities->addColumns($this, $this->getFieldIdRenderer(), $this->getFieldSourceRenderer());
        $this->_addAfter = false;
    }

    /**
     * Retrieve a renderer for the Field ID
     *
     * @return FlexibleFieldId
     */
    private function getFieldIdRenderer()
    {
        if (!$this->fieldIdRenderer) {
            $this->fieldIdRenderer = $this->utilities->buildFieldIdRenderer();
        }

        return $this->fieldIdRenderer;
    }

    /**
     * Retrieve a renderer for the Field Source
     *
     * @return FlexibleFieldSource
     */
    private function getFieldSourceRenderer()
    {
        if (!$this->fieldSourceRenderer) {
            $this->fieldSourceRenderer = $this->utilities->buildFieldSourceRenderer();
            $this->fieldSourceRenderer->setType(FlexibleFieldSource::TYPE_CODE);
        }

        return $this->fieldSourceRenderer;
    }
}
