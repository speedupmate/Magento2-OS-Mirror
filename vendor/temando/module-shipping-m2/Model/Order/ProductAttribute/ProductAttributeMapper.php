<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order\ProductAttribute;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Api\Attribute\Mapping\ProductManagementInterface;
use Temando\Shipping\Model\Attribute\Mapping\Product as MappingProduct;
use Temando\Shipping\Model\ResourceModel\Repository\AttributeMappingProductRepositoryInterface;

/**
 * Temando Order Product Attribute Mapper
 *
 * Maps available product attributes
 *
 * @package Temando\Shipping\Model
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ProductAttributeMapper
{
    /**
     * @var ProductManagementInterface
     */
    private $productManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var AttributeMappingProductRepositoryInterface
     */
    private $productAttributeMappingRepository;

    /**
     * @var QuoteItemAttributeReader
     */
    private $quoteItemAttributeReader;

    /**
     * @var OrderItemAttributeReader
     */
    private $orderItemAttributeReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductAttributeMapper constructor.
     *
     * @param ProductManagementInterface $productManagement
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeManagementInterface $attributeManagement
     * @param AttributeMappingProductRepositoryInterface $productAttributeMappingRepository
     * @param QuoteItemAttributeReader $quoteItemAttributeReader
     * @param OrderItemAttributeReader $orderItemAttributeReader
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductManagementInterface $productManagement,
        ProductRepositoryInterface $productRepository,
        AttributeManagementInterface $attributeManagement,
        AttributeMappingProductRepositoryInterface $productAttributeMappingRepository,
        QuoteItemAttributeReader $quoteItemAttributeReader,
        OrderItemAttributeReader $orderItemAttributeReader,
        LoggerInterface $logger
    ) {
        $this->productManagement = $productManagement;
        $this->productRepository = $productRepository;
        $this->attributeManagement = $attributeManagement;
        $this->productAttributeMappingRepository = $productAttributeMappingRepository;
        $this->quoteItemAttributeReader = $quoteItemAttributeReader;
        $this->orderItemAttributeReader = $orderItemAttributeReader;
        $this->logger = $logger;
    }

    /**
     * Gets Attribute Data Mapped to Node Path ID
     *
     * @param Product $product
     * @return array
     */
    public function getMappedProductAttributes(Product $product): array
    {
        try {
            /** @var Product $actualProduct */
            $actualProduct = $this->productRepository->getById($product->getId());
        } catch (NoSuchEntityException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return [];
        }

        /** @var AbstractAttribute[] $productAttributes */
        $productAttributes = $actualProduct->getAttributes();

        $mappedAttributeValues = [];
        $mappableAttributeKeys = $this->productAttributeMappingRepository->getMappedAttributes();

        foreach ($mappableAttributeKeys as $attribute) {
            $isDefault = $attribute[MappingProduct::IS_DEFAULT];
            $key = $attribute[MappingProduct::NODE_PATH_ID];
            $mappedAttributeId = $attribute[MappingProduct::MAPPED_ATTRIBUTE_ID];

            if (!$productAttribute = $this->getProductAttribute($mappedAttributeId, $key, $productAttributes)) {
                continue;
            }

            $value = $this->getProductAttributeValue($actualProduct, $productAttribute, $mappedAttributeId);

            if ($isDefault) {
                $mappedAttributeValues[$key] = $value;
            } else {
                $mappedAttributeValues[MappingProduct::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX][$key] = $value;
            }
        }

        return $mappedAttributeValues;
    }

    /**
     * Get the product attribute.
     *
     * @param string $mappedAttributeId
     * @param string $nodePathId
     * @param \Magento\Eav\Model\Entity\Attribute\AttributeInterface[] $productAttributes
     * @return mixed|null
     */
    private function getProductAttribute($mappedAttributeId, $nodePathId, $productAttributes)
    {
        if (array_key_exists($mappedAttributeId, $productAttributes)) {
            return $productAttributes[$mappedAttributeId];
        } else {
            // Raise warning for attribute that does not exist
            $this->logger->warning(
                __(
                    'Attribute %1 does not exist for mapping to %2',
                    $mappedAttributeId,
                    $nodePathId
                )
            );
        }

        return null;
    }

    /**
     * Get the product attribute value.
     *
     * @param Product $product
     * @param Attribute $productAttribute
     * @param string $attributeId
     * @return mixed
     */
    private function getProductAttributeValue(
        Product $product,
        Attribute $productAttribute,
        $attributeId
    ) {
        $productAttributeFrontEndType = $productAttribute->getFrontendInput();
        $productAttributeBackendType = $productAttribute->getBackendType();

        if ($productAttributeFrontEndType === 'select' && $productAttributeBackendType === 'varchar') {
            // Select and Varchar account for getting country codes over the full country name
            $value = $product->getDataByKey($attributeId);
        } elseif ($attributeId === 'category_ids') {
            /**
             * Category names instead of IDs
             * @var Collection $categoryCollection
             */
            $categoryCollection = $product->getCategoryCollection();
            $categoryCollection->addNameToResult();
            $categoryNames = $categoryCollection->getColumnValues('name');
            $value = (is_array($categoryNames)) ? implode(',', $categoryNames) : null;
        } else {
            $value = $productAttribute->getFrontend()->getValue($product);
        }

        return $this->validateProductAttributeValue($value, $attributeId);
    }

    /**
     * Validate the value type is able to be used.
     *
     * @param string $value
     * @param string $attributeId
     * @return mixed
     */
    private function validateProductAttributeValue($value, $attributeId)
    {
        $valueType = gettype($value);

        // Raise warning for unsupported mappings
        switch ($valueType) {
            case 'array':
            case 'object':
            case 'resource':
                $this->logger->warning(
                    __(
                        'Cannot map attribute %1 due to attribute type %2',
                        $attributeId,
                        $valueType
                    )
                );

                return '';
        }

        return $value;
    }

    /**
     * Add child mapped attributes to parent product mapped attributes.
     *
     * @param array $mappedAttributes
     * @param Product $product
     * @return array
     */
    private function addChildMappedAttributes(array $mappedAttributes, Product $product): array
    {
        $childMappedAttributes = $this->getMappedProductAttributes($product);

        array_walk($childMappedAttributes, function ($item, $key) use (&$mappedAttributes) {
            if ($key === MappingProduct::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX) {
                foreach ($item as $custom => $value) {
                    if ($value) {
                        $mappedAttributes[MappingProduct::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX][$custom] = $value;
                    }
                }

                return;
            }

            if ($item) {
                $mappedAttributes[$key] = $item;
            }
        });

        return $mappedAttributes;
    }

    /**
     * Get the mapped product attributes from a quote item.
     *
     * @param QuoteItem $item
     * @return array
     */
    public function getMappedProductAttributesFromQuoteItem(QuoteItem $item): array
    {
        $selectedProduct = $this->quoteItemAttributeReader->getSelectedProduct($item);
        $mappedProductAttributes = $this->getMappedProductAttributes($selectedProduct);

        $children = $item->getChildren();
        if (is_array($children) && !empty($children)) {
            foreach ($children as $child) {
                $childProduct = $child->getProduct();

                $mappedProductAttributes = $this->addChildMappedAttributes(
                    $mappedProductAttributes,
                    $childProduct
                );
            }
        }

        return $mappedProductAttributes;
    }

    /**
     * Get the mapped product attributes from an order item.
     *
     * @param OrderItem $item
     * @return array
     */
    public function getMappedProductAttributesFromOrderItem(OrderItem $item): array
    {
        $selectedProduct = $this->orderItemAttributeReader->getSelectedProduct($item);
        $mappedProductAttributes = $this->getMappedProductAttributes($selectedProduct);

        $children = $item->getChildrenItems();
        if (is_array($children) && !empty($children)) {
            foreach ($children as $child) {
                $childProduct = $child->getProduct();

                $mappedProductAttributes = $this->addChildMappedAttributes(
                    $mappedProductAttributes,
                    $childProduct
                );
            }
        }

        return $mappedProductAttributes;
    }
}
