<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;
use Magento\Quote\Api\CartItemRepositoryInterface;

/**
 * @inheritdoc
 */
class QuoteItemGetterProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    const PREFIX = 'quote_line_item';
    const BLACK_LIST = [
        'getExtensionAttributes',
        'getShortDescription',
        'getParentCode',
        'getTaxClassKey',
        'getSku',
        'getQty'
    ];
    const DATE_FIELDS = [
        'getCreatedAt',
        'getUpdatedAt',
    ];

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var ValueExtractor */
    private $valueExtractor;

    /** @var CartItemRepositoryInterface */
    private $cartItemRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param ValueExtractor $valueExtractor
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        FlexFieldProcessableAttributeFactory $attributeFactory,
        ValueExtractor $valueExtractor,
        CartItemRepositoryInterface $cartItemRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->attributeFactory = $attributeFactory;
        $this->valueExtractor = $valueExtractor;
        $this->cartItemRepository = $cartItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        // Generate attributes from OrderItem's getters
        $attributes = $this->attributeExtractor->extract(
            CartItemInterface::class,
            static::PREFIX,
            'Quote Item',
            static::class,
            array_merge(static::DATE_FIELDS, static::BLACK_LIST)
        );

        // Date fields can't be auto-generated (as Magento's interfaces don't have a date type)
        foreach (static::DATE_FIELDS as $dateField) {
            $attributeCode = static::PREFIX . ".{$dateField}";
            /** @var FlexFieldProcessableAttribute $attribute */
            $attribute = $this->attributeFactory->create();
            $attribute->setAttributeCode($attributeCode);
            $attribute->setLabel(substr(preg_replace('/[A-Z]/', ' $0', $dateField), 4));
            $attribute->setOptionGroup('Quote Item');
            $attribute->setType(FlexibleFieldSource::TYPE_DATE);
            $attribute->setProcessor(static::class);
            $attributes[$attributeCode] = $attribute;
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getValueFromQuote(QuoteDetailsItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes || !$extAttributes->getQuoteItemId() || !$extAttributes->getQuoteId()) {
            return null;
        }

        return $this->getValueFromQuoteItemId(
            $extAttributes->getQuoteId(),
            $extAttributes->getQuoteItemId(),
            $attributeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $order = $this->orderRepository->get($item->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromQuoteItemId(
            $order->getQuoteId(),
            $item->getQuoteItemId(),
            $attributeCode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $order = $this->orderRepository->get($orderItem->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromQuoteItemId(
            $order->getQuoteId(),
            $orderItem->getQuoteItemId(),
            $attributeCode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromCreditmemo(CreditmemoItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $order = $this->orderRepository->get($orderItem->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromQuoteItemId(
            $order->getQuoteId(),
            $orderItem->getQuoteItemId(),
            $attributeCode
        );
    }

    /**
     * Extract the value of a getter given a Quote Item ID and attribute code
     *
     * @param int $quoteId
     * @param int $quoteItemId
     * @param string $attributeCode
     * @return int|string|null
     */
    private function getValueFromQuoteItemId($quoteId, $quoteItemId, $attributeCode)
    {
        try {
            $cartItem = $this->getCartItem(
                $this->cartItemRepository->getList($quoteId),
                $quoteItemId
            );
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->valueExtractor->extract($cartItem, $attributeCode, static::PREFIX, static::DATE_FIELDS);
    }

    /**
     * Return cart item instance
     *
     * @param array $cartItems
     * @param int $cartItemId
     * @return CartItemInterface
     * @throws NoSuchEntityException
     */
    private function getCartItem($cartItems, $cartItemId)
    {
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getItemId() == $cartItemId) {
                return $cartItem;
            }
        }
        throw NoSuchEntityException::singleField('cartId', $cartItemId);
    }
}
