<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;

/**
 * @inheritDoc
 */
class QuoteGetterProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    const BLACK_LIST = [
        'getItems',
        'getCustomer',
        'getBillingAddress',
        'getCurrency',
        'getExtensionAttributes',
        'getCustomerTaxClassId'
    ];
    const DATE_FIELDS = [
        'getCreatedAt',
        'getUpdatedAt',
        'getConvertedAt',
    ];
    const PREFIX = 'quote';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var ValueExtractor */
    private $valueExtractor;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param ValueExtractor $valueExtractor
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        ValueExtractor $valueExtractor,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->cartRepository = $cartRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return array_merge(
            $this->attributeExtractor->extract(
                CartInterface::class,
                static::PREFIX,
                'Quote',
                static::class,
                array_merge(static::DATE_FIELDS, static::BLACK_LIST)
            ),
            $this->attributeExtractor->extractDateFields(
                static::PREFIX,
                static::DATE_FIELDS,
                'Quote',
                static::class
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromCreditmemo(CreditmemoItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getValueFromOrder(
            $this->orderItemRepository->get($item->getOrderItemId()),
            $attributeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getValueFromOrder(
            $this->orderItemRepository->get($item->getOrderItemId()),
            $attributeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $order = $this->orderRepository->get($item->getOrderId());
        $quoteId = $order->getQuoteId();

        return $this->getValueFromQuoteId($quoteId, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromQuote(QuoteDetailsItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        if (!$item->getExtensionAttributes()) {
            return null;
        }
        return $this->getValueFromQuoteId($item->getExtensionAttributes()->getQuoteId(), $attributeCode);
    }

    /**
     * Extract the value of a getter given a Quote ID and attribute code
     *
     * @param string $quoteId
     * @param string $attributeCode
     * @return mixed
     */
    private function getValueFromQuoteId($quoteId, $attributeCode)
    {
        try {
            $cart = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->valueExtractor->extract($cart, $attributeCode, static::PREFIX, static::DATE_FIELDS);
    }
}
