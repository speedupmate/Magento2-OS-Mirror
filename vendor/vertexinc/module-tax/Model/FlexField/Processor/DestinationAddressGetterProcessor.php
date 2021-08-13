<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Exception;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavAttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavValueExtractor;

/**
 * @inheritDoc
 */
class DestinationAddressGetterProcessor implements
    InvoiceFlexFieldProcessorInterface,
    TaxCalculationFlexFieldProcessorInterface
{
    /** @var string[] */
    const BLACK_LIST = [
        'getExtensionAttributes',
        'getCustomAttribute',
        'getCustomAttributes',
        'getVatId',
        'getCreatedAt',
        'getUpdatedAt',
        'getStreet'
    ];

    /** @var string */
    const PREFIX = 'destination_address';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var EavAttributeExtractor */
    private $eavAttributeExtractor;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var EavValueExtractor */
    private $valueExtractor;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param EavAttributeExtractor $eavAttributeExtractor
     * @param EavValueExtractor $valueExtractor
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        EavAttributeExtractor $eavAttributeExtractor,
        EavValueExtractor $valueExtractor,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->eavAttributeExtractor = $eavAttributeExtractor;
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
        $optionGroup = 'Destination Address';

        return $this->eavAttributeExtractor->extract(
                'customer_address',
                static::PREFIX,
                $optionGroup,
                static::class
            );
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
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromOrderId($orderItem->getOrderId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromOrderId($orderItem->getOrderId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getValueFromOrderId($item->getOrderId(), $attributeCode);
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
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes || !$extAttributes->getQuoteId()) {
            return null;
        }

        try {
            $cart = $this->cartRepository->get($extAttributes->getQuoteId());
            $address = $this->getAddressFromQuote($cart);

            if ($address === null) {
                return null;
            }

            return $this->valueExtractor->extract(
                $address,
                'customer_address',
                $attributeCode,
                static::PREFIX,
                $this->getCustomDateAttributes()
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract the value of a getter given a Order ID and attribute code
     *
     * @param int $orderId
     * @param string $attributeCode
     * @return int|string|null
     */
    private function getValueFromOrderId($orderId, $attributeCode)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $address = $this->getAddressFromOrder($order);

            if ($address === null) {
                return null;
            }

            return $this->valueExtractor->extract(
                $address,
                'customer_address',
                $attributeCode,
                static::PREFIX,
                $this->getCustomDateAttributes()
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve the destination address from a Quote
     *
     * @param CartInterface $cart
     * @return AddressInterface|null
     */
    private function getAddressFromQuote(CartInterface $cart)
    {
        if (!$cart->getIsVirtual()) {
            return $cart->getShippingAddress()
                ? $cart->getShippingAddress()
                : $this->getShippingAssignmentAddress($cart);
        }
        return $cart->getBillingAddress();
    }

    /**
     * Retrieve the destination address from an Order
     *
     * @param OrderInterface $order
     * @return OrderAddressInterface|null
     */
    private function getAddressFromOrder(OrderInterface $order)
    {
        if (!$order->getIsVirtual()) {
            return $order->getShippingAddress()?: $this->getShippingAssignmentAddress($order);
        }
        return $order->getBillingAddress();
    }

    /**
     * Retrieve the shipping address from the shipping assignments
     *
     * @param CartInterface|OrderInterface $object
     * @return AddressInterface|OrderAddressInterface|null
     */
    private function getShippingAssignmentAddress($object)
    {
        if (!$object instanceof ExtensibleDataInterface) {
            return null;
        }

        return $object->getExtensionAttributes() !== null
        && $object->getExtensionAttributes()->getShippingAssignments()
        && $object->getExtensionAttributes()->getShippingAssignments()[0]
        && $object->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()
            ? $object->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()->getAddress()
            : null;
    }

    /**
     * Retrieve a list of custom date attribute codes
     *
     * @return array
     */
    private function getCustomDateAttributes()
    {
        return $this->eavAttributeExtractor->getCustomDateAttributeCodes('customer_address');
    }
}
