<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * @inheritdoc
 */
class OrderItemGetterProcessor implements InvoiceFlexFieldProcessorInterface
{
    const BLACK_LIST = [
        'getAdditionalData',
        'getExtensionAttributes',
        'getAppliedRuleIds',
        'getProductOption',
        'getParentItem',
    ];
    const DATE_FIELDS = [
        'getCreatedAt',
        'getUpdatedAt',
    ];
    const PREFIX = 'order_line_item';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var ValueExtractor */
    private $valueExtractor;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ValueExtractor $valueExtractor
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        OrderItemRepositoryInterface $orderItemRepository,
        ValueExtractor $valueExtractor
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->orderItemRepository = $orderItemRepository;
        $this->valueExtractor = $valueExtractor;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return array_merge(
            $this->attributeExtractor->extract(
                OrderItemInterface::class,
                static::PREFIX,
                'Order Item',
                static::class,
                array_merge(static::DATE_FIELDS, static::BLACK_LIST)
            ),
            $this->attributeExtractor->extractDateFields(
                static::PREFIX,
                static::DATE_FIELDS,
                'Order Item',
                static::class
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $orderItem = $this->orderItemRepository->get($item->getOrderItemId());

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->valueExtractor->extract($item, $attributeCode, static::PREFIX, static::DATE_FIELDS);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromCreditmemo(CreditmemoItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $orderItem = $this->orderItemRepository->get($item->getOrderItemId());

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }
}
