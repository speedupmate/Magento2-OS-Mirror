<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Contains logic common to processing items on Invoices and Creditmemos
 */
class ItemProcessor
{
    /** @var SearchCriteriaBuilderFactory */
    private $criteriaBuilderFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var OrderItemRepositoryInterface  */
    private $orderItemRepository;

    public function __construct(
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        ProductRepositoryInterface $productRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Fetch all utilized products from the database by SKU
     *
     * This was previously done by ID, but we had an issue with configurable products
     * in that the child did not have the total but the parent did, and the parent
     * was attached to the parent product.  This isn't bueno, since we need
     * to use the child's tax class for records.
     *
     * So.. that didn't work.  Instead, we're using SKU.  In the vast majority
     * of scenarios the SKU should not change on the product.
     *
     * The correct way to "fix" this would be to attach the necessary product
     * data to the order, and subsequently the invoice at the time of creation,
     * however we'd still have a problem if that data were missing
     * (b/c Vertex was disabled or older versions or any number of scenarios)
     *
     * @param string[] $productSku
     * @return ProductInterface[] Indexed by sku
     */
    public function getProductsIndexedBySku(array $productSku)
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter(OrderItemInterface::SKU, $productSku, 'in');
        $criteria = $criteriaBuilder->create();

        $items = $this->orderItemRepository->getList($criteria)->getItems();

        $productIds = [];
        foreach ($items as $item) {
            if ($item->getBaseRowTotal() === null) {
                continue;
            }
            $productIds[$item->getProductId()] = $item->getProductId();
        }

        $productCriteriaBuilder = $this->criteriaBuilderFactory->create();
        $productCriteriaBuilder->addFilter('entity_id', $productIds, 'in');

        $productSearchCriteria = $productCriteriaBuilder->create();
        $products = $this->productRepository->getList($productSearchCriteria)->getItems();

        /** @var ProductInterface[] $products */
        return array_reduce(
            $products,
            function (array $carry, ProductInterface $product) {
                $carry[$product->getId()] = $product;
                return $carry;
            },
            []
        );
    }
}
