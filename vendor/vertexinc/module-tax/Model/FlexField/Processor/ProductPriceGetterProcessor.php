<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * @inheritDoc
 */
class ProductPriceGetterProcessor implements
    InvoiceFlexFieldProcessorInterface,
    TaxCalculationFlexFieldProcessorInterface
{
    const ATTRIBUTE_CODE = 'order_product_price.price';

    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /**
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     */
    public function __construct(FlexFieldProcessableAttributeFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $attributes = [];

        $attribute = $this->attributeFactory->create();
        $attribute->setAttributeCode(static::ATTRIBUTE_CODE);
        $attribute->setLabel(__('Price')->render());
        $attribute->setOptionGroup(__('Product')->render());
        $attribute->setType(FlexibleFieldSource::TYPE_NUMERIC);
        $attribute->setProcessor(static::class);

        $attributes[static::ATTRIBUTE_CODE] = $attribute;

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        return $item->getPrice();
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $item->getPrice();
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $item->getPrice();
    }

    /**
     * @inheritdoc
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        return $item->getUnitPrice();
    }
}
