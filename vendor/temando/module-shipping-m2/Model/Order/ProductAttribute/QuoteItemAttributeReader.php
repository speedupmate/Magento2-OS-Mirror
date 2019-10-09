<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order\ProductAttribute;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Quote\Model\Quote\Item\Option;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Setup\SetupData;

/**
 * Temando Quote Item Attribute Reader
 *
 * Access product attribute from quote item.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QuoteItemAttributeReader
{
    /**
     * Obtain the actual product added to cart, i.e. the chosen configuration.
     *
     * Will return parent product details if required.
     *
     * @param ItemInterface $quoteItem
     * @return Product
     */
    public function getSelectedProduct(ItemInterface $quoteItem): Product
    {
        $simpleOption = $quoteItem->getOptionByCode('simple_product');
        if ($simpleOption instanceof Option) {
            $product = $simpleOption->getProduct();
            if ($this->canInheritPackagingDetails($product)) {
                $product = $quoteItem->getProduct();
            }
        } else {
            $product = $quoteItem->getProduct();
        }
        return $product;
    }

    /**
     * Determines if the selected product is able to inherit packaging details from its parent
     *
     * @param Product $product
     * @return bool
     */
    private function canInheritPackagingDetails(Product $product): bool
    {
        $packagingType = $product->getData(SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE);
        if (empty($packagingType) || $packagingType == PackagingType::PACKAGING_TYPE_NONE) {
            return true;
        }

        return false;
    }

    /**
     * Obtain the categories the product is assigned to.
     *
     * @param ItemInterface $quoteItem
     * @return string[]
     */
    public function getCategoryNames(ItemInterface $quoteItem): array
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $quoteItem->getProduct()->getCategoryCollection();
        $categoryCollection->addNameToResult();
        $categoryNames = $categoryCollection->getColumnValues('name');

        return $categoryNames;
    }

    /**
     * Check if the quote item was created from a virtual product.
     *
     * @param ItemInterface $quoteItem
     * @return bool
     */
    public function isVirtual(ItemInterface $quoteItem): bool
    {
        return $quoteItem->getProduct()->getIsVirtual();
    }

    /**
     * Get the product's length dimension.
     *
     * @param ItemInterface $quoteItem
     * @return float
     */
    public function getLength(ItemInterface $quoteItem): float
    {
        $product = $this->getSelectedProduct($quoteItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_LENGTH);
    }

    /**
     * Get the product's width dimension.
     *
     * @param ItemInterface $quoteItem
     * @return float
     */
    public function getWidth(ItemInterface $quoteItem): float
    {
        $product = $this->getSelectedProduct($quoteItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_WIDTH);
    }

    /**
     * Get the product's height dimension.
     *
     * @param ItemInterface $quoteItem
     * @return float
     */
    public function getHeight(ItemInterface $quoteItem): float
    {
        $product = $this->getSelectedProduct($quoteItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_HEIGHT);
    }

    /**
     * Check if the product is pre-packaged. Fall back to the configurable's attribute if necessary.
     *
     * Note: At the platform, both "packed" and "assigned" are flagged as "isPrePackaged".
     *
     * @param ItemInterface $quoteItem
     * @return bool
     */
    public function isPrePackaged(ItemInterface $quoteItem): bool
    {
        $product = $this->getSelectedProduct($quoteItem);
        $packagingType = $product->getData(SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE);
        return in_array($packagingType, [PackagingType::PACKAGING_TYPE_PACKED, PackagingType::PACKAGING_TYPE_ASSIGNED]);
    }

    /**
     * Get package id for pre packaged items. Fall back to the configurable's attribute if necessary.
     *
     * @param ItemInterface $quoteItem
     * @return string
     */
    public function getPackageId(ItemInterface $quoteItem): string
    {
        /** @var Product $product */
        $product = $this->getSelectedProduct($quoteItem);
        return (string) $product->getData(SetupData::ATTRIBUTE_CODE_PACKAGING_ID);
    }
}
