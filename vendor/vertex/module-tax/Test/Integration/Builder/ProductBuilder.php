<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\Builder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;

/**
 * Build a product with stock
 */
class ProductBuilder
{
    const EXAMPLE_PRODUCT_ATTRIBUTE_SET = 4;
    const EXAMPLE_PRODUCT_NAME = 'Example Product';
    const EXAMPLE_PRODUCT_PRICE = 5.00;
    const EXAMPLE_PRODUCT_SKU = 'TEST';
    const EXAMPLE_PRODUCT_STATUS = Status::STATUS_ENABLED;
    const EXAMPLE_PRODUCT_TYPE = Type::TYPE_SIMPLE;
    const EXAMPLE_PRODUCT_VISIBILITY = Visibility::VISIBILITY_BOTH;

    /** @var ProductInterfaceFactory */
    private $productFactory;

    /** @var ProductRepositoryInterface */
    private $productRepositoryFactory;

    /** @var StockRegistryInterface */
    private $stockRegistry;

    /** @var StockRegistryStorage */
    private $stockRegistryStorage;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param StockRegistryInterface $stockRegistry
     * @param StockRegistryStorage $stockRegistryStorage
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        StockRegistryInterface $stockRegistry,
        StockRegistryStorage $stockRegistryStorage
    ) {
        $this->productFactory = $productFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->stockRegistry = $stockRegistry;
        $this->stockRegistryStorage = $stockRegistryStorage;
    }

    /**
     * Create an example product including stock
     *
     * Performs 3 database queries
     *
     * @param callable $productConfiguration Receives 1 parameter of ProductInterface.  Should return a ProductInterface
     * @param bool $isInStock
     * @param int $stockQty
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createExampleProduct(callable $productConfiguration = null, $isInStock = true, $stockQty = 500)
    {
        return $this->createProduct(
            function (ProductInterface $product) use ($productConfiguration) {
                $product->setName(static::EXAMPLE_PRODUCT_NAME);
                $product->setSku(static::EXAMPLE_PRODUCT_SKU);
                $product->setPrice(static::EXAMPLE_PRODUCT_PRICE);
                $product->setVisibility(static::EXAMPLE_PRODUCT_VISIBILITY);
                $product->setStatus(static::EXAMPLE_PRODUCT_STATUS);
                $product->setTypeId(static::EXAMPLE_PRODUCT_TYPE);
                $product->setAttributeSetId(static::EXAMPLE_PRODUCT_ATTRIBUTE_SET);

                return $productConfiguration !== null ? $productConfiguration($product) : $product;
            },
            $isInStock,
            $stockQty
        );
    }

    /**
     * Create a product including stock
     *
     * Performs 3 database queries.
     *
     * @param callable $productConfiguration Receives 1 parameter of ProductInterface.  Should return a ProductInterface
     * @param bool $isInStock
     * @param int $stockQty
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createProduct(callable $productConfiguration, $isInStock = true, $stockQty = 500)
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $product = $productConfiguration($product);
        if (!($product instanceof ProductInterface)) {
            throw new \TypeError('Result of createProduct callback must return a ProductInterface');
        }

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->productRepositoryFactory->create();

        $product = $productRepository->save($product);

        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setIsInStock($isInStock);
        $stockItem->setQty($stockQty);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

        // Reload product in such a way as to re-generate saleability data
        $this->stockRegistryStorage->removeStockStatus($product->getId());
        $this->stockRegistryStorage->removeStockItem($product->getId());
        $product = $productRepository->get($product->getSku());

        return $product;
    }
}
