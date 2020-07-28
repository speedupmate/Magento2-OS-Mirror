<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\Processor\FlexFieldAttributeProcessor;
use Vertex\Tax\Model\FlexField\Processor\InvoiceFlexFieldProcessorInterface;
use Vertex\Tax\Model\FlexField\Processor\TaxCalculationFlexFieldProcessorInterface;

/**
 * HTML select for flexible field data source
 */
class FlexibleFieldSource extends Select
{
    /** Code-type flexible field */
    const TYPE_CODE = FlexFieldProcessableAttribute::TYPE_CODE;

    /** Date-type flex field */
    const TYPE_DATE = FlexFieldProcessableAttribute::TYPE_DATE;

    /** Numeric-type flex field */
    const TYPE_NUMERIC = FlexFieldProcessableAttribute::TYPE_NUMERIC;

    /** @var FlexFieldAttributeProcessor */
    private $flexFieldAttributeProcessor;

    /**
     * @param Context $context
     * @param FlexFieldAttributeProcessor $flexFieldAttributeProcessor
     * @param array $data
     */
    public function __construct(
        Context $context,
        FlexFieldAttributeProcessor $flexFieldAttributeProcessor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->flexFieldAttributeProcessor = $flexFieldAttributeProcessor;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getOptions()
    {
        $allAttributes = [['value' => 'none', 'label' => 'No Data']];

        $attributes = $this->flexFieldAttributeProcessor->getAttributes();

        $attributes = array_filter(
            $attributes,
            function (FlexFieldProcessableAttribute $attribute) {
                return $this->getType() === $attribute->getType();
            }
        );

        foreach ($attributes as $attribute) {
            if (!isset($allAttributes[$attribute->getOptionGroup()])) {
                $allAttributes[$attribute->getOptionGroup()] = [
                    'value' => [],
                    'label' => $attribute->getOptionGroup(),
                ];
            }

            $allAttributes[$attribute->getOptionGroup()]['value'][] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getLabel(),
            ];
        }

        return $allAttributes;
    }

    /**
     * Retrieve the type of flexible field
     *
     * @return string One of {@see FlexibleFieldSource::TYPE_CODE}, {@see FlexibleFieldSource::TYPE_DATE},
     *  {@see FlexibleFieldSource::TYPE_NUMERIC}
     */
    public function getType()
    {
        return $this->getData('flexible_field_type');
    }

    /**
     * Set the name of the input
     *
     * @param string $name
     * @return FlexibleFieldSource
     */
    public function setInputName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Set the type of flexible field
     *
     * @param string $type One of {@see FlexibleFieldSource::TYPE_CODE}, {@see FlexibleFieldSource::TYPE_DATE},
     *  {@see FlexibleFieldSource::TYPE_NUMERIC}
     * @return FlexibleFieldSource
     */
    public function setType($type)
    {
        $this->setData('flexible_field_type', $type);

        return $this;
    }
}
