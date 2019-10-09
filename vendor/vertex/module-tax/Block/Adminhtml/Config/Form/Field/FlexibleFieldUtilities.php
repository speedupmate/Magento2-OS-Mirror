<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;

/**
 * Utilities for the various Flexible Field configuration tables
 */
class FlexibleFieldUtilities
{
    /** @var LayoutInterface */
    private $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Add fieldId and fieldSource columns
     *
     * @param AbstractFieldArray $block
     * @param FlexibleFieldId $idRenderer
     * @param FlexibleFieldSource $sourceRenderer
     * @return void
     */
    public function addColumns(
        AbstractFieldArray $block,
        FlexibleFieldId $idRenderer,
        FlexibleFieldSource $sourceRenderer
    ) {
        $block->addColumn(
            'field_id',
            [
                'label' => __('Field ID'),
                'renderer' => $idRenderer,
            ]
        );
        $block->addColumn(
            'field_source',
            [
                'label' => __('Data Source'),
                'renderer' => $sourceRenderer,
            ]
        );
    }

    /**
     * Build the Flexible Field ID Renderer
     *
     * @param array $data
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function buildFieldIdRenderer($data = [])
    {
        return $this->layout->createBlock(
            FlexibleFieldId::class,
            '',
            [
                'data' => array_merge(
                    [
                        'is_render_to_js_template' => true
                    ],
                    $data
                ),
            ]
        );
    }

    /**
     * Build the Flexible Field Source Renderer
     *
     * @param array $data
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function buildFieldSourceRenderer($data = [])
    {
        return $this->layout->createBlock(
            FlexibleFieldSource::class,
            '',
            [
                'data' => array_merge(
                    [
                        'is_render_to_js_template' => true,
                    ],
                    $data
                ),
            ]
        );
    }

    /**
     * Prepare a row in a table
     *
     * Sets the selected option for the field source
     *
     * @param DataObject $row
     * @param FlexibleFieldSource $fieldSourceRenderer
     * @return void
     */
    public function prepareArrayRow(DataObject $row, FlexibleFieldSource $fieldSourceRenderer)
    {
        $fieldSource = $row->getData('field_source');
        $options = [];
        if ($fieldSource) {
            $name = 'option_' . $fieldSourceRenderer->calcOptionHash($fieldSource);
            $options[$name] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
