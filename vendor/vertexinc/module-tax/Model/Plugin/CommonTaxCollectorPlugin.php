<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Vertex\Tax\Model\Api\Utility\IsVirtualLineItemDeterminer;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Plugins to the Common Tax Collector
 *
 * @see CommonTaxCollector
 */
class CommonTaxCollectorPlugin
{
    /** @var Config */
    private $config;

    /** @var SearchCriteriaBuilderFactory */
    private $criteriaBuilderFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    /** @var IsVirtualLineItemDeterminer */
    private $virtualLineDeterminer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        TaxClassNameRepository $taxClassNameRepository,
        IsVirtualLineItemDeterminer $virtualLineDeterminer,
        Config $config
    ) {
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->virtualLineDeterminer = $virtualLineDeterminer;
    }

    /**
     * Add a created SKU for shipping to the QuoteDetailsItem
     *
     * @see CommonTaxCollector::getShippingDataObject()
     */
    public function afterGetShippingDataObject(
        CommonTaxCollector $subject,
        ?QuoteDetailsItemInterface $itemDataObject,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): ?QuoteDetailsItemInterface {
        $store = $this->getStoreCodeFromShippingAssignment($shippingAssignment);
        if ($itemDataObject === null
            || !$this->config->isVertexActive($store) || !$this->config->isTaxCalculationEnabled($store)) {
            return $itemDataObject;
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            return $itemDataObject;
        }

        if ($shipping->getMethod() === null && $total->getShippingTaxCalculationAmount() == 0) {
            // If there's no method and a $0 price then there's no need for an empty shipping tax item
            return null;
        }

        $extensionAttributes = $itemDataObject->getExtensionAttributes();
        $extensionAttributes->setVertexProductCode($shippingAssignment->getShipping()->getMethod());

        return $itemDataObject;
    }

    /**
     * Add VAT ID to Address used in Tax Calculation
     *
     * @see CommonTaxCollector::mapAddress()
     */
    public function afterMapAddress(
        CommonTaxCollector $subject,
        AddressInterface $customerAddress,
        Address $address
    ): AddressInterface {
        $customerAddress->setVatId($address->getVatId());
        return $customerAddress;
    }

    /**
     * Add Vertex data to QuoteDetailsItems
     *
     * @see CommonTaxCollector::mapItem()
     */
    public function afterMapItem(
        CommonTaxCollector $subject,
        QuoteDetailsItemInterface $taxData,
        QuoteDetailsItemInterfaceFactory $dataObjectFactory,
        AbstractItem $item
    ): QuoteDetailsItemInterface {
        if (!$this->config->isVertexActive($item->getStoreId())) {
            return $taxData;
        }

        $extensionData = $taxData->getExtensionAttributes();
        try {
            $product = $this->productRepository->get($item->getProduct()->getSku());
            $commodityCode = $product->getExtensionAttributes()->getVertexCommodityCode();
            if ($commodityCode) {
                $extensionData->setVertexCommodityCode($commodityCode);
            }
        } catch (NoSuchEntityException $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            /* Fake product, Exception expected, NOOP for commodity code lookup */
        }
        $extensionData->setVertexProductCode($item->getProduct()->getSku());
        $extensionData->setVertexIsConfigurable($item->getProduct()->getTypeId() === 'configurable');
        $extensionData->setStoreId($item->getStore()->getStoreId());
        $extensionData->setProductId($item->getProduct()->getId());
        $extensionData->setQuoteItemId($item->getId());
        $extensionData->setCustomerId($item->getQuote()->getCustomerId());
        $extensionData->setIsVirtual($this->virtualLineDeterminer->isCartItemVirtual($item));

        if ($quote = $item->getQuote()) {
            $extensionData->setQuoteId($quote->getId());
            $extensionData->setCustomerId($quote->getCustomerId());
        }

        return $taxData;
    }

    /**
     * Add a created SKU and update the tax class of Item-level Giftwrap
     *
     * @param CommonTaxCollector $subject
     * @param QuoteDetailsItemInterface[] $quoteItems
     * @param QuoteDetailsItemInterfaceFactory $dataObjectFactory
     * @param AbstractItem $item
     * @return QuoteDetailsItemInterface[]
     * @see CommonTaxCollector::mapItemExtraTaxables()
     */
    public function afterMapItemExtraTaxables(
        CommonTaxCollector $subject,
        array $quoteItems,
        QuoteDetailsItemInterfaceFactory $dataObjectFactory,
        AbstractItem $item
    ): array {
        $store = $item->getStore();
        if (!$this->config->isVertexActive($store->getStoreId())) {
            return $quoteItems;
        }

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getType() !== 'item_gw' &&
                ($quoteItem->getType() === 'weee' && !$this->config->isVertexFixedProductTaxCustom())) {
                continue;
            }
            $gwPrefix = '';

            if ($quoteItem->getType() === 'item_gw') {
                $taxClassId = $this->config->getGiftWrappingItemClass($store);
                $gwPrefix = $this->config->getGiftWrappingItemCodePrefix($store);
            }

            if ($quoteItem->getType() === 'weee') {
                $taxClassId = $this->config->vertexTaxClassUsedForFixedProductTax();
            }

            $productSku = $item->getProduct()->getSku();

            // Set the Product Code
            $extensionData = $quoteItem->getExtensionAttributes();
            $extensionData->setVertexProductCode($gwPrefix . $productSku);

            // Change the Tax Class ID
            $quoteItem->setTaxClassId($taxClassId);
            $taxClassKey = $quoteItem->getTaxClassKey();
            if ($taxClassKey && $taxClassKey->getType() === TaxClassKeyInterface::TYPE_ID) {
                $quoteItem->getTaxClassKey()->setValue($taxClassId);
            }
        }

        return $quoteItems;
    }

    /**
     * Fetch and store the tax class of the child of any configurable products mapped
     *
     * Steps we take:
     * 1. Reduce the items to process from all items to those that are configurable products
     * 2. Retrieve an array of those items SKUs - due to the nature of configurable products, they will be the
     *    simple's sku
     * 3. Fetch all products for items we want to process
     * 4. Create a mapping of product sku -> tax class id
     * 5. Fetch all tax class names
     * 6. Go through the product sku mapping and override the tax class ids on the parent products' items
     *
     * @param CommonTaxCollector $subject
     * @param QuoteDetailsItemInterface[] $items
     * @return QuoteDetailsItemInterface[]
     * @see CommonTaxCollector::mapItems()
     */
    public function afterMapItems(CommonTaxCollector $subject, array $items): array
    {
        // Manually providing the store ID is not necessary
        if (!$this->config->isVertexActive()) {
            return $items;
        }

        $result = array_reduce(
            $items,
            static function ($result, QuoteDetailsItemInterface $item) {
                if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getVertexIsConfigurable()) {
                    $code = strtoupper($item->getExtensionAttributes()->getVertexProductCode());
                    $result['processItems'][$code] = $item;
                    $result['productCodes'][] = $code;
                }
                return $result;
            },
            ['processItems' => [], 'productCodes' => []]
        );

        /** @var QuoteDetailsItemInterface[] $processItems indexed by product sku */
        $processItems = $result['processItems'];

        /** @var string[] $productCodes List of SKUs we want to know the tax classes of */
        $productCodes = $result['productCodes'];

        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter(ProductInterface::SKU, $productCodes, 'in');
        $criteria = $criteriaBuilder->create();
        $products = $this->productRepository->getList($criteria)->getItems();

        /** @var int[] $productCodeTaxClassMap Mapping of product sku (key) to tax class IDs */
        $productCodeTaxClassMap = [];

        /** @var ProductInterface[] $products */
        foreach ($products as $product) {
            $attribute = $product->getCustomAttribute('tax_class_id');
            $taxClassId = $attribute ? $attribute->getValue() : null;
            $productCodeTaxClassMap[strtoupper($product->getSku())] = $taxClassId;
        }

        /** @var int[] $taxClassIds */
        $taxClassIds = array_values($productCodeTaxClassMap);
        $taxClasses = $this->taxClassNameRepository->getListByIds($taxClassIds);

        foreach ($productCodeTaxClassMap as $productCode => $taxClassId) {
            $processItems[$productCode]->setTaxClassId($taxClasses[$taxClassId]);
            $processItems[$productCode]->getTaxClassKey()->setValue($taxClassId);
        }

        return $items;
    }

    /**
     * Retrieve the Store ID from a Shipping Assignment
     *
     * This is the same way the Magento_Tax module gets the store when its needed - we have a problem, though, where
     * getQuote isn't part of the AddressInterface, and I don't particularly trust all the getters to not unexpectedly
     * return NULL.
     */
    private function getStoreCodeFromShippingAssignment(
        ?ShippingAssignmentInterface $shippingAssignment = null
    ): ?int {
        return $shippingAssignment !== null
        && $shippingAssignment->getShipping() !== null
        && $shippingAssignment->getShipping()->getAddress() !== null
        && method_exists($shippingAssignment->getShipping()->getAddress(), 'getQuote')
        && $shippingAssignment->getShipping()->getAddress()->getQuote() !== null
            ? (int)$shippingAssignment->getShipping()->getAddress()->getQuote()->getStoreId()
            : null;
    }
}
