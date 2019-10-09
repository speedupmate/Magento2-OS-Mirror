<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Flexfield\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * @inheritDoc
 */
class OrderCurrencyGetterProcessor implements
    InvoiceFlexFieldProcessorInterface,
    TaxCalculationFlexFieldProcessorInterface
{
    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        FlexFieldProcessableAttributeFactory $attributeFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        $attributeCode = 'order_currency.getStoreCurrencyCode';

        $attribute = $this->attributeFactory->create();
        $attribute->setAttributeCode($attributeCode);
        $attribute->setLabel('Currency Code');
        $attribute->setOptionGroup('Order');
        $attribute->setType(FlexibleFieldSource::TYPE_CODE);
        $attribute->setProcessor(static::class);

        return [
            $attributeCode => $attribute,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $orderItem = $this->orderItemRepository->get($item->getOrderItemId());

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $orderItem = $this->orderItemRepository->get($item->getOrderItemId());

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $order = $this->orderRepository->get($item->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $order->getOrderCurrencyCode();
    }

    /**
     * @inheritDoc
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $quoteId = $item->getExtensionAttributes() && $item->getExtensionAttributes()->getQuoteId()
            ? $item->getExtensionAttributes()->getQuoteId()
            : null;

        if ($quoteId === null) {
            return null;
        }

        try {
            $cart = $this->cartRepository->get($quoteId);
            /** @var CurrencyInterface */
            $currency = $cart->getCurrency();
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $currency ? $currency->getQuoteCurrencyCode() : null;
    }
}
