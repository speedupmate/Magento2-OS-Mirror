<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavAttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavValueExtractor;

/**
 * @inheritDoc
 */
class CustomerGetterProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    /** @var string[] */
    const BLOCK_LIST = [
        'getExtensionAttributes',
        'getCustomAttribute',
        'getCustomAttributes',
        'getAddresses',
        'getDefaultBilling',
        'getDefaultShipping',
        'getConfirmation',
        'getDisableAutoGroupChange',
        'getCreatedIn',
        'getEmail',
        'getFirstname',
        'getLastname',
        'getMiddlename',
        'getPrefix',
        'getSuffix',
        'getTaxvat',
        'getId',
        'getGroupId',
        'getStoreId',
        'getWebsiteId',
        'getCreatedAt',
        'getUpdatedAt'
    ];

    /** @var string[] */
    const DATE_FIELDS = [
        'getDob'
    ];

    /** @var string[] */
    const EAV_ATTRIBUTE_BLOCK_LIST = [
        'reward_update_notification',
        'reward_warning_notification'
    ];

    /** @var string */
    const PREFIX = 'customer';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var AttributeRenamer */
    private $attributeRenamer;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var EavAttributeExtractor */
    private $eavAttributeExtractor;

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
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param AttributeRenamer $attributeRenamer
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        EavAttributeExtractor $eavAttributeExtractor,
        EavValueExtractor $valueExtractor,
        CustomerRepositoryInterface $customerRepository,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        AttributeRenamer $attributeRenamer
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->eavAttributeExtractor = $eavAttributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->attributeRenamer = $attributeRenamer;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributeRenamer->execute(
            array_merge(
                $this->attributeExtractor->extract(
                    CustomerInterface::class,
                    static::PREFIX,
                    'Customer',
                    static::class,
                    array_merge(static::DATE_FIELDS, static::BLOCK_LIST)
                ),
                $this->attributeExtractor->extractDateFields(
                    static::PREFIX,
                    static::DATE_FIELDS,
                    'Customer',
                    static::class
                ),
                $this->eavAttributeExtractor->extract(
                    Customer::ENTITY,
                    static::PREFIX,
                    'Customer',
                    static::class,
                    static::EAV_ATTRIBUTE_BLOCK_LIST
                )
            ),
            [static::PREFIX . '.getDob' => __('Birthdate')]
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
            $order = $this->orderRepository->get($orderItem->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromCustomerId($order->getCustomerId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $order = $this->orderRepository->get($orderItem->getOrderId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getValueFromCustomerId($order->getCustomerId(), $attributeCode);
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

        return $this->getValueFromCustomerId($order->getCustomerId(), $attributeCode);
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
        if (!$extAttributes || !$extAttributes->getCustomerId()) {
            return null;
        }

        return $this->getValueFromCustomerId($extAttributes->getCustomerId(), $attributeCode);
    }

    /**
     * Retrieve a list of custom date attribute codes
     *
     * @return array
     */
    private function getCustomDateAttributes()
    {
        return $this->eavAttributeExtractor->getCustomDateAttributeCodes(Customer::ENTITY);
    }

    /**
     * Extract the value of a getter given a Customer ID and attribute code
     *
     * @param int $customerId
     * @param string $attributeCode
     * @return int|string|null
     */
    private function getValueFromCustomerId($customerId, $attributeCode)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $this->valueExtractor->extract(
                $customer,
                Customer::ENTITY,
                $attributeCode,
                static::PREFIX,
                array_merge(static::DATE_FIELDS, $this->getCustomDateAttributes())
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
