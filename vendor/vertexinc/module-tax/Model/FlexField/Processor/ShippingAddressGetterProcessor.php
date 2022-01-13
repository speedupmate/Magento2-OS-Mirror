<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\Api\Utility\IsVirtualLineItemDeterminer;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavAttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavValueExtractor;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;

/**
 * @inheritDoc
 */
class ShippingAddressGetterProcessor implements
    InvoiceFlexFieldProcessorInterface,
    TaxCalculationFlexFieldProcessorInterface
{
    /** @var string[] */
    const BLOCK_LIST = [
        'getExtensionAttributes',
        'getCustomAttribute',
        'getCustomAttributes',
        'getVatId',
        'getCreatedAt',
        'getUpdatedAt',
        'getStreet',
        'getRegion',
        'getCountryId',
        'getCity',
        'getPostcode',
        'getTelephone',
        'getFax',
        'getPrefix',
        'getSuffix',
        'getRegionCode',
        'getId',
        'getEmail',
        'getRegionId',
        'getCustomerId',
        'getSameAsBilling',
        'getCustomerAddressId',
        'getSaveInAddressBook'
    ];

    /** @var string */
    const PREFIX = 'shipping_address';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var AttributeRenamer */
    private $attributeRenamer;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var EavAttributeExtractor */
    private $eavAttributeExtractor;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var ShippingAddressRetriever */
    private $shippingAddressRetriever;

    /** @var EavValueExtractor */
    private $valueExtractor;

    /** @var IsVirtualLineItemDeterminer */
    private $isVirtualLineItemDeterminer;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param EavAttributeExtractor $eavAttributeExtractor
     * @param EavValueExtractor $valueExtractor
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ShippingAddressRetriever $shippingAddressRetriever
     * @param AttributeRenamer $attributeRenamer
     * @param IsVirtualLineItemDeterminer $isVirtualLineItemDeterminer
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        EavAttributeExtractor $eavAttributeExtractor,
        EavValueExtractor $valueExtractor,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ShippingAddressRetriever $shippingAddressRetriever,
        AttributeRenamer $attributeRenamer,
        IsVirtualLineItemDeterminer $isVirtualLineItemDeterminer
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->eavAttributeExtractor = $eavAttributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->cartRepository = $cartRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->shippingAddressRetriever = $shippingAddressRetriever;
        $this->attributeRenamer = $attributeRenamer;
        $this->isVirtualLineItemDeterminer = $isVirtualLineItemDeterminer;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $optionGroup = 'Shipping Address';

        /** @var FlexFieldProcessableAttribute[] $results */
        return $this->attributeRenamer->execute(
            array_merge(
                $this->attributeExtractor->extract(
                    AddressInterface::class,
                    static::PREFIX,
                    $optionGroup,
                    static::class,
                    static::BLOCK_LIST
                ),
                $this->eavAttributeExtractor->extract(
                    'customer_address',
                    static::PREFIX,
                    $optionGroup,
                    static::class
                )
            ),
            [
                static::PREFIX . '.getFirstname' => __('First Name'),
                static::PREFIX . '.getLastname' => __('Last Name'),
                static::PREFIX . '.getMiddlename' => __('Middle Name'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        if ($this->isVirtualLineItemDeterminer->isCreditMemoItemVirtual($item)) {
            return null;
        }

        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            return $this->getValueFromOrderId($orderItem->getOrderId(), $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        if ($this->isVirtualLineItemDeterminer->isInvoiceItemVirtual($item)) {
            return null;
        }

        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            return $this->getValueFromOrderId($orderItem->getOrderId(), $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        if ($this->isVirtualLineItemDeterminer->isOrderItemVirtual($item)) {
            return null;
        }
        return $this->getValueFromOrderId($item->getOrderId(), $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes
            || !$extAttributes->getQuoteId()
            || $this->isVirtualLineItemDeterminer->isQuoteDetailsItemVirtual($item)
        ) {
            return null;
        }

        try {
            $cart = $this->cartRepository->get($extAttributes->getQuoteId());
            $address = $this->shippingAddressRetriever->getShippingFromQuote($cart);

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
     * Retrieve a list of custom date attribute codes
     *
     * @return array
     */
    private function getCustomDateAttributes()
    {
        return $this->eavAttributeExtractor->getCustomDateAttributeCodes('customer_address');
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
            $address = $this->shippingAddressRetriever->getShippingFromOrder($order);

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
}
