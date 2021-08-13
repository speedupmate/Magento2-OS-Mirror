<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\ProductLoadIdResolverInterface;
use Vertex\Tax\Model\Repository\CommodityCodeProductRepository;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CommodityCodeProduct;
use Vertex\Tax\Model\Data\CommodityCodeProductFactory;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Handle extension attribute commodity code for saving product
 *
 * @see ProductRepositoryInterface
 */
class CommodityCodeExtensionAttributeProductRepositoryPlugin
{
    /** @var CommodityCodeProductFactory */
    private $factory;

    /** @var Config */
    protected $config;

    /** @var ProductLoadIdResolverInterface */
    private $loadIdResolver;

    /** @var CommodityCodeProductRepository */
    protected $repository;

    /** @var ExceptionLogger */
    protected $logger;

    public function __construct(
        CommodityCodeProductRepository $repository,
        CommodityCodeProductFactory $factory,
        ExceptionLogger $logger,
        Config $config,
        ProductLoadIdResolverInterface $loadIdResolver
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->logger = $logger;
        $this->config = $config;
        $this->loadIdResolver = $loadIdResolver;
    }

    /**
     * Intercept product deleting in order to delete Commodity Code.
     *
     * @see ProductRepositoryInterface::delete()
     * @param ProductRepositoryInterface $subject
     * @param bool $result
     * @param ProductInterface $product
     * @return bool
     */
    public function afterDelete(
        ProductRepositoryInterface $subject,
        bool $result,
        ProductInterface $product
    ): bool {
        $productId = $this->loadIdResolver->execute($product);
        $this->deleteByProductId($productId);

        return $result;
    }

    /**
     * Intercept product get in order to load commodity code
     *
     * @see ProductRepositoryInterface::get()
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @return ProductInterface
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        $sku,
        $editMode = false,
        $storeId = null
    ): ProductInterface {
        $productId = $this->loadIdResolver->execute($result);
        return $this->afterGetById($subject, $result, $productId, $editMode, $storeId);
    }

    /**
     * Intercept product get in order to load commodity code
     *
     * @see ProductRepositoryInterface::getById()
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param int $productId
     * @param bool $editMode
     * @param int|null $storeId
     * @return ProductInterface
     */
    public function afterGetById(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        $productId,
        $editMode = false,
        $storeId = null
    ): ProductInterface {
        if (!$this->config->isVertexActive($storeId)) {
            return $result;
        }

        $extensionAttributes = $result->getExtensionAttributes();
        $productId = $this->loadIdResolver->execute($result);

        try {
            $commodityCode = $this->repository->getByProductId($productId);
            $extensionAttributes->setVertexCommodityCode($commodityCode);
        } catch (NoSuchEntityException $exception) {
            $commodityCode = null;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Intercept list in order to add Commodity Code to products
     *
     * @see ProductRepositoryInterface::getList()
     * @param ProductRepositoryInterface $subject
     * @param ProductSearchResultsInterface $results
     * @return ProductSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(ProductRepositoryInterface $subject, $results)
    {
        if (!$this->config->isVertexActive() || $results->getTotalCount() <= 0) {
            return $results;
        }

        $productIds = array_map(
            static function (ProductInterface $product) {
                return $product->getId();
            },
            $results->getItems()
        );

        $commodityCodes = $this->repository->getListByProductIds($productIds);

        foreach ($results->getItems() as $product) {
            $extensionAttributes = $product->getExtensionAttributes();
            $productId = $this->loadIdResolver->execute($product);

            if (!isset($commodityCodes[$productId])) {
                continue;
            }

            $extensionAttributes->setVertexCommodityCode($commodityCodes[$productId]);
        }

        return $results;
    }

    /**
     * Save Commodity Code extension attribute
     *
     * @see ProductRepositoryInterface::save()
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param ProductInterface $product
     * @return ProductInterface
     */
    public function afterSave(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        ProductInterface $product
    ): ProductInterface {
        if (!$this->config->isVertexActive()) {
            return $result;
        }

        if ($product->getExtensionAttributes()) {
            $commodityCode = $product->getExtensionAttributes()->getVertexCommodityCode();
            $productId = $this->loadIdResolver->execute($result);

            if ($commodityCode) {
                $commodityCodeProduct = $this->getCodeModel($productId);
                $commodityCodeProduct->setCode($commodityCode->getCode());
                $commodityCodeProduct->setType($commodityCode->getType());

                try {
                    $this->repository->save($commodityCodeProduct);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                $this->deleteByProductId($productId);
            }
        }

        return $result;
    }

    /**
     * Delete a Commodity Code given a Product ID
     *
     * @param int $productId
     * @return void
     */
    private function deleteByProductId($productId)
    {
        try {
            $this->repository->deleteByProductId($productId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve the Commodity Code by Product ID
     *
     * @param int $productId
     * @return CommodityCodeProduct
     */
    private function getCodeModel($productId)
    {
        try {
            $commodityCode = $this->repository->getByProductId($productId);
        } catch (NoSuchEntityException $e) {
            /** @var CommodityCodeProduct $commodityCode */
            $commodityCode = $this->factory->create();
            $commodityCode->setProductId($productId);
        }

        return $commodityCode;
    }
}
