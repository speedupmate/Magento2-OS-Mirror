<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\ValueExtractor;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * @inheritDoc
 */
class StoreGetterProcessor implements
    InvoiceFlexFieldProcessorInterface,
    TaxCalculationFlexFieldProcessorInterface
{
    /** @var string[] */
    const DATE_FIELDS = [];
    const PREFIX = 'store';

    /** @var string[] */
    const ALLOW_LIST = [
        'getId',
        'getCode'
    ];

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var AttributeRenamer */
    private $attributeRenamer;

    /** @var string[] List of non date allowed methods to be selected in */
    private $blockListMethods;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var ValueExtractor */
    private $valueExtractor;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param ValueExtractor $valueExtractor
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param AttributeRenamer $attributeRenamer
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        ValueExtractor $valueExtractor,
        OrderItemRepositoryInterface $orderItemRepository,
        StoreRepositoryInterface $storeRepository,
        AttributeRenamer $attributeRenamer
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->orderItemRepository = $orderItemRepository;
        $this->valueExtractor = $valueExtractor;
        $this->storeRepository = $storeRepository;
        $this->attributeRenamer = $attributeRenamer;
    }

    /**
     * Retrieve all available attributes
     *
     * @return FlexFieldProcessableAttribute[]
     */
    public function getAttributes()
    {

        $blocklistMethods = $this->getBlockListMethods();

        /** @var FlexFieldProcessableAttribute[] $results */
        return $this->attributeRenamer->execute(
            array_merge(
                $this->attributeExtractor->extract(
                    StoreInterface::class,
                    static::PREFIX,
                    'Store',
                    static::class,
                    array_merge(static::DATE_FIELDS, $blocklistMethods)
                ),
                $this->attributeExtractor->extractDateFields(
                    static::PREFIX,
                    static::DATE_FIELDS,
                    'Store',
                    static::class
                )
            ),
            [static::PREFIX . '.getId' => __('Store ID')]
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
        $orderItemId = $item->getOrderItemId();
        $orderItem = $this->orderItemRepository->get($orderItemId);

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $orderItemId = $item->getOrderItemId();
        $orderItem = $this->orderItemRepository->get($orderItemId);

        return $this->getValueFromOrder($orderItem, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        /** @var StoreInterface $store */
        $store = $this->getStoreById($item->getStoreId());

        if ($store === null) {
            return null;
        }

        return $this->valueExtractor->extract($store, $attributeCode, static::PREFIX, static::DATE_FIELDS);
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
        $storeId = $item->getExtensionAttributes() && $item->getExtensionAttributes()->getStoreId()
            ? $item->getExtensionAttributes()->getStoreId()
            : null;

        $store = $storeId ? $this->getStoreById($storeId) : null;

        if ($store === null) {
            return null;
        }

        return $this->valueExtractor->extract($store, $attributeCode, static::PREFIX, static::DATE_FIELDS);
    }

    /**
     * Returns an array of all attribute methods that cannot be used in this processor
     *
     * @return string[]
     */
    private function getBlockListMethods()
    {
        if (empty($this->blockListMethods)) {
            $allowlist = static::ALLOW_LIST;

            $this->blockListMethods = array_filter(
                get_class_methods(StoreInterface::class),
                static function ($methodName) use ($allowlist) {
                    return !in_array($methodName, $allowlist, true) && strpos($methodName, 'get') === 0;
                }
            );
        }
        return $this->blockListMethods;
    }

    /**
     * Returns a Store object by it's id
     *
     * @param int $id Store id number
     * @return StoreInterface
     */
    private function getStoreById($id)
    {
        try {
            return $this->storeRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
