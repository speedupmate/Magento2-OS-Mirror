<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order\ProductAttribute;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Api\Data\OrderItemInterface;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Setup\SetupData;

/**
 * Temando Order Item Attribute Reader
 *
 * Access product attribute from order item.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderItemAttributeReader
{
    /**
     * Obtain the actual product added to cart, i.e. the chosen configuration.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return Product
     */
    public function getSelectedProduct(OrderItemInterface $orderItem): Product
    {
        if ($orderItem->getProductType() === Configurable::TYPE_CODE) {
            $childItem = current($orderItem->getChildrenItems());
            $product = $childItem->getProduct();
            if ($this->canInheritPackagingDetails($product)) {
                $product = $orderItem->getProduct();
            }
        } else {
            $product = $orderItem->getProduct();
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
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return string[]
     */
    public function getCategoryNames(OrderItemInterface $orderItem): array
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $orderItem->getProduct()->getCategoryCollection();
        $categoryCollection->addNameToResult();
        $categoryNames = $categoryCollection->getColumnValues('name');

        return $categoryNames;
    }

    /**
     * Check if the quote item was created from a virtual product.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return bool
     */
    public function isVirtual(OrderItemInterface $orderItem): bool
    {
        return $orderItem->getProduct()->getIsVirtual();
    }

    /**
     * Get the product's length dimension.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return float
     */
    public function getLength(OrderItemInterface $orderItem): float
    {
        $product = $this->getSelectedProduct($orderItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_LENGTH);
    }

    /**
     * Get the product's width dimension.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return float
     */
    public function getWidth(OrderItemInterface $orderItem): float
    {
        $product = $this->getSelectedProduct($orderItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_WIDTH);
    }

    /**
     * Get the product's height dimension.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return float
     */
    public function getHeight(OrderItemInterface $orderItem): float
    {
        $product = $this->getSelectedProduct($orderItem);
        return (float) $product->getData(SetupData::ATTRIBUTE_CODE_HEIGHT);
    }

    /**
     * Check if the product is pre-packaged. Fall back to the configurable's attribute if necessary.
     *
     * Note: At the platform, both "packed" and "assigned" are flagged as "isPrePackaged".
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return bool
     */
    public function isPrePackaged(OrderItemInterface $orderItem): bool
    {
        $product = $this->getSelectedProduct($orderItem);
        $packagingType = $product->getData(SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE);
        return in_array($packagingType, [PackagingType::PACKAGING_TYPE_PACKED, PackagingType::PACKAGING_TYPE_ASSIGNED]);
    }

    /**
     * Get package id for pre packaged items. Fall back to the configurable's attribute if necessary.
     *
     * @param OrderItemInterface|\Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getPackageId(OrderItemInterface $orderItem): string
    {
        $product = $this->getSelectedProduct($orderItem);
        return (string) $product->getData(SetupData::ATTRIBUTE_CODE_PACKAGING_ID);
    }
}
