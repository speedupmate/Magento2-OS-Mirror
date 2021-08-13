<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\ProductLoadIdResolverInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CommodityCodeProduct;
use Vertex\Tax\Model\Data\CommodityCodeProductFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\CommodityCodeProductRepository;

/**
 * Handle extension attribute commodity code for saving product
 *
 * @see ProductResourceModel
 */
class CommodityCodeExtensionAttributeProductResourceModelPlugin
{
    /** @var CommodityCodeProductFactory */
    private $factory;

    /** @var Config */
    private $config;

    /** @var ProductLoadIdResolverInterface */
    private $loadIdResolver;

    /** @var CommodityCodeProductRepository */
    private $repository;

    /** @var ExceptionLogger */
    private $logger;

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
     * Intercept resource model save in order to persist Commodity Code
     *
     * @see ProductResourceModel::save()
     * @param ProductResourceModel $subject
     * @param ProductResourceModel $result
     * @param Product $product
     * @return ProductResourceModel
     */
    public function afterSave(
        ProductResourceModel $subject,
        ProductResourceModel $result,
        Product $product
    ): ProductResourceModel {
        if (!$this->config->isVertexActive()) {
            return $result;
        }

        $commodityCodeData = $product->getData('vertex_commodity_code');

        $productId = $this->loadIdResolver->execute($product);
        if ($commodityCodeData) {
            $codeModel = $this->getCodeModel($productId);
            $codeModel->setCode($commodityCodeData['code']);
            $codeModel->setType($commodityCodeData['type']);

            try {
                $this->repository->save($codeModel);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        } else {
            $this->deleteByProductId($productId);
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
