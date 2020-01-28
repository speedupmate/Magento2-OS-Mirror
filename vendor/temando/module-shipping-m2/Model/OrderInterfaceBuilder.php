<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model;

use Magento\Directory\Model\Currency;
use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\Checkout\RateRequest\Extractor;
use Temando\Shipping\Model\Order\CheckoutFieldContainerInterface;
use Temando\Shipping\Model\Order\CheckoutFieldContainerInterfaceBuilder;
use Temando\Shipping\Model\Order\CustomAttributesInterfaceBuilder;
use Temando\Shipping\Model\Order\OrderBillingInterfaceBuilder;
use Temando\Shipping\Model\Order\OrderItemInterfaceBuilder;
use Temando\Shipping\Model\Order\OrderRecipientInterfaceBuilder;
use Temando\Shipping\Model\Shipping\ItemExtractor;

/**
 * Temando Order Interface Builder
 *
 * Create an entity to be shared between shipping module and Temando platform.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderInterfaceBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var Extractor
     */
    private $rateRequestExtractor;

    /**
     * @var ItemExtractor
     */
    private $itemExtractor;

    /**
     * @var OrderBillingInterfaceBuilder
     */
    private $billingBuilder;

    /**
     * @var OrderRecipientInterfaceBuilder
     */
    private $recipientBuilder;

    /**
     * @var OrderItemInterfaceBuilder
     */
    private $orderItemBuilder;

    /**
     * @var CheckoutFieldContainerInterfaceBuilder
     */
    private $checkoutFieldContainerBuilder;

    /**
     * @var CustomAttributesInterfaceBuilder
     */
    private $customAttributesBuilder;

    /**
     * OrderInterfaceBuilder constructor.
     * @param ObjectFactory $objectFactory
     * @param Extractor $rateRequestExtractor
     * @param ItemExtractor $itemExtractor
     * @param OrderBillingInterfaceBuilder $billingBuilder
     * @param OrderRecipientInterfaceBuilder $recipientBuilder
     * @param OrderItemInterfaceBuilder $orderItemBuilder
     * @param CheckoutFieldContainerInterfaceBuilder $checkoutFieldContainerBuilder
     * @param CustomAttributesInterfaceBuilder $customAttributesBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        Extractor $rateRequestExtractor,
        ItemExtractor $itemExtractor,
        OrderBillingInterfaceBuilder $billingBuilder,
        OrderRecipientInterfaceBuilder $recipientBuilder,
        OrderItemInterfaceBuilder $orderItemBuilder,
        CheckoutFieldContainerInterfaceBuilder $checkoutFieldContainerBuilder,
        CustomAttributesInterfaceBuilder $customAttributesBuilder
    ) {
        $this->rateRequestExtractor = $rateRequestExtractor;
        $this->itemExtractor = $itemExtractor;
        $this->billingBuilder = $billingBuilder;
        $this->recipientBuilder = $recipientBuilder;
        $this->orderItemBuilder = $orderItemBuilder;
        $this->checkoutFieldContainerBuilder = $checkoutFieldContainerBuilder;
        $this->customAttributesBuilder = $customAttributesBuilder;

        parent::__construct($objectFactory);
    }

    /**
     * Map order state code to platform order status.
     * Valid values: "awaiting payment" "confirmed" "processing" "fulfilled" "cancelled" "archived" "refunded" "closed"
     *
     * @param string $state
     * @return string
     */
    private function mapOrderState($state)
    {
        $map = [
            Order::STATE_NEW => OrderInterface::STATUS_CONFIRMED,
            Order::STATE_PENDING_PAYMENT => OrderInterface::STATUS_AWAITING_PAYMENT,
            Order::STATE_PROCESSING => OrderInterface::STATUS_PROCESSING,
            Order::STATE_COMPLETE => OrderInterface::STATUS_CLOSED,
            Order::STATE_CLOSED => OrderInterface::STATUS_REFUNDED,
            Order::STATE_CANCELED => OrderInterface::STATUS_CANCELLED,
            Order::STATE_HOLDED => OrderInterface::STATUS_ARCHIVED,
            Order::STATE_PAYMENT_REVIEW => OrderInterface::STATUS_AWAITING_PAYMENT,
            '--' => OrderInterface::STATUS_FULFILLED // shipments created: no corresponding order state available
        ];

        return isset($map[$state]) ? $map[$state] : '';
    }

    /**
     * @param RateRequest $rateRequest
     * @return void
     */
    public function setRateRequest(RateRequest $rateRequest)
    {
        try {
            $quote = $this->rateRequestExtractor->getQuote($rateRequest);
            $createdAt = $quote->getCreatedAt();

            $updatedAt = (strpos($quote->getUpdatedAt(), '0000') === 0)
                ? $quote->getCreatedAt()
                : $quote->getUpdatedAt();

            // orderedAt is a required field, although this does not make sense during checkout.
            $orderedAt = $quote->getConvertedAt()
                ? $quote->getConvertedAt()
                : $updatedAt;

            $sourceReference = $quote->getReservedOrderId()
                ? $quote->getReservedOrderId()
                : $quote->getId();
        } catch (LocalizedException $e) {
            // detailed order data unavailable
            $createdAt = gmdate('Y-m-d H:i:s');
            $updatedAt = $createdAt;
            $orderedAt = $createdAt;
            $sourceReference = '';
        }

        $currencyCode = $rateRequest->getBaseCurrency();
        if ($currencyCode instanceof Currency) {
            $currencyCode = $currencyCode->getCurrencyCode();
        }

        $this->billingBuilder->setRateRequest($rateRequest);
        $billingAddress = $this->billingBuilder->create();

        $this->recipientBuilder->setRateRequest($rateRequest);
        $recipient = $this->recipientBuilder->create();

        $rateRequestItems = $this->rateRequestExtractor->getItems($rateRequest);
        $shippableItems = $this->itemExtractor->extractShippableQuoteItems($rateRequestItems);
        $orderItems = array_map(function (AbstractItem $quoteItem) use ($rateRequest) {
            $this->orderItemBuilder->setRateRequest($rateRequest);
            $this->orderItemBuilder->setQuoteItem($quoteItem);
            return $this->orderItemBuilder->create();
        }, $shippableItems);

        // add data path to checkout fields by reading definitions from config
        $this->checkoutFieldContainerBuilder->setRateRequest($rateRequest);
        /** @var CheckoutFieldContainerInterface $checkoutFieldContainer */
        $checkoutFieldContainer = $this->checkoutFieldContainerBuilder->create();

        $this->customAttributesBuilder->setRateRequest($rateRequest);
        $customAttributes = $this->customAttributesBuilder->create();

        $this->_set(OrderInterface::CREATED_AT, $createdAt);
        $this->_set(OrderInterface::ORDERED_AT, $orderedAt);
        $this->_set(OrderInterface::LAST_MODIFIED_AT, $updatedAt);
        $this->_set(OrderInterface::STATUS, '');
        $this->_set(OrderInterface::BILLING, $billingAddress);
        $this->_set(OrderInterface::RECIPIENT, $recipient);
        $this->_set(OrderInterface::ORDER_ITEMS, $orderItems);
        $this->_set(OrderInterface::CURRENCY, $currencyCode);
        $this->_set(OrderInterface::AMOUNT, $rateRequest->getPackageValueWithDiscount());
        $this->_set(OrderInterface::CHECKOUT_FIELDS, $checkoutFieldContainer->getFields());
        $this->_set(OrderInterface::CUSTOM_ATTRIBUTES, $customAttributes);
        $this->_set(OrderInterface::SOURCE_REFERENCE, $sourceReference);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     * @return void
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $shippingMethod = $order->getShippingMethod(true);
        $methodCode = $shippingMethod->getData('method');

        $shippingAssignments = $order->getExtensionAttributes()->getShippingAssignments();
        if (!empty($shippingAssignments)) {
            $orderId = $shippingAssignments[0]->getShipping()->getExtensionAttributes()->getExtOrderId();
        } else {
            $orderId = '';
        }

        $this->billingBuilder->setOrder($order);
        $billingAddress = $this->billingBuilder->create();

        $this->recipientBuilder->setOrder($order);
        $recipient = $this->recipientBuilder->create();

        $shippableItems = $this->itemExtractor->extractShippableOrderItems($order->getAllVisibleItems());
        $orderItems = array_map(function (OrderItemInterface $orderItem) use ($order) {
            $this->orderItemBuilder->setOrder($order);
            $this->orderItemBuilder->setOrderItem($orderItem);
            return $this->orderItemBuilder->create();
        }, $shippableItems);

        if (strpos($order->getUpdatedAt(), '0000') === 0) {
            $updatedAt = $order->getCreatedAt();
        } else {
            $updatedAt = $order->getUpdatedAt();
        }

        // add data path to checkout fields by reading definitions from config
        $this->checkoutFieldContainerBuilder->setOrder($order);
        /** @var CheckoutFieldContainerInterface $checkoutFieldContainer */
        $checkoutFieldContainer = $this->checkoutFieldContainerBuilder->create();

        $this->customAttributesBuilder->setOrder($order);
        $customAttributes = $this->customAttributesBuilder->create();

        $this->_set(OrderInterface::ORDER_ID, $orderId);
        $this->_set(OrderInterface::CREATED_AT, $order->getCreatedAt());
        $this->_set(OrderInterface::ORDERED_AT, $order->getCreatedAt());
        $this->_set(OrderInterface::LAST_MODIFIED_AT, $updatedAt);
        $this->_set(OrderInterface::STATUS, $this->mapOrderState($order->getState()));
        $this->_set(OrderInterface::BILLING, $billingAddress);
        $this->_set(OrderInterface::RECIPIENT, $recipient);
        $this->_set(OrderInterface::ORDER_ITEMS, $orderItems);
        $this->_set(OrderInterface::CURRENCY, $order->getBaseCurrencyCode());
        $this->_set(OrderInterface::AMOUNT, $order->getBaseGrandTotal());
        $this->_set(OrderInterface::CHECKOUT_FIELDS, $checkoutFieldContainer->getFields());
        $this->_set(OrderInterface::CUSTOM_ATTRIBUTES, $customAttributes);
        $this->_set(OrderInterface::SOURCE_REFERENCE, $order->getIncrementId());
        $this->_set(OrderInterface::SOURCE_ID, $order->getEntityId());
        $this->_set(OrderInterface::SOURCE_INCREMENT_ID, $order->getIncrementId());
        $this->_set(OrderInterface::SELECTED_EXPERIENCE_CODE, $methodCode);
        $this->_set(OrderInterface::SELECTED_EXPERIENCE_CURRENCY, $order->getBaseCurrencyCode());
        $this->_set(OrderInterface::SELECTED_EXPERIENCE_AMOUNT, $order->getBaseShippingAmount());
        $this->_set(OrderInterface::SELECTED_EXPERIENCE_LANGUAGE, 'en');
        $this->_set(OrderInterface::SELECTED_EXPERIENCE_DESCRIPTION, $order->getShippingDescription());
    }

    /**
     * @param CollectionPointSearchRequestInterface $searchRequest
     * @return void
     */
    public function setCollectionPointSearchRequest(CollectionPointSearchRequestInterface $searchRequest)
    {
        $this->_set(OrderInterface::COLLECTION_POINT_SEARCH_REQUEST, $searchRequest);
    }

    /**
     * @param QuoteCollectionPointInterface $collectionPoint
     * @return void
     */
    public function setCollectionPoint(QuoteCollectionPointInterface $collectionPoint)
    {
        $this->_set(OrderInterface::COLLECTION_POINT, $collectionPoint);
    }

    /**
     * @param PickupLocationSearchRequestInterface $isPickupLocationSearchRequest
     * @return void
     */
    public function setPickupLocationSearchRequest(PickupLocationSearchRequestInterface $isPickupLocationSearchRequest)
    {
        $this->_set(OrderInterface::PICKUP_LOCATION_SEARCH_REQUEST, $isPickupLocationSearchRequest);
    }

    /**
     * @param QuotePickupLocationInterface $pickupLocation
     * @return void
     */
    public function setPickupLocation(QuotePickupLocationInterface $pickupLocation)
    {
        $this->_set(OrderInterface::PICKUP_LOCATION, $pickupLocation);
    }
}
