<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CommodityCodeOrderItem;
use Vertex\Tax\Model\Data\CommodityCodeOrderItemFactory;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Include the Vertex Commodity Code to the order item
 *
 * @see OrderInterface
 */
class AddCommodityCodeToOrderItemPlugin
{
    /** @var Config */
    private $config;

    /** @var ExceptionLogger */
    private $logger;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CommodityCodeOrderItemFactory */
    private $commodityCodeOrderItemFactory;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepositoryInterface;

    public function __construct(
        Config $config,
        ExceptionLogger $logger,
        ProductRepositoryInterface $productRepository,
        CommodityCodeOrderItemFactory $commodityCodeOrderItemFactory,
        OrderItemRepositoryInterface $orderItemRepositoryInterface
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->commodityCodeOrderItemFactory = $commodityCodeOrderItemFactory;
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
    }

    /**
     * Save the commodity code to the order item
     *
     * @see OrderInterface::save()
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $result
    ): OrderInterface {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        foreach ($result->getItems() as $item) {
            try {
                $product = $this->productRepository->get($item->getSku());
                $commodityCode = $this->getCommodityCodeFromProduct($product);

                if ($commodityCode) {
                    $commodityCode->setOrderItemId($item->getItemId());
                    $item->getExtensionAttributes()->setVertexCommodityCode($commodityCode);
                    $this->orderItemRepositoryInterface->save($item);
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        return $result;
    }

    /**
     * Get Commodity Code data from product and assign to CommodityCode OrderItem
     *
     * @param ProductInterface $product
     * @return CommodityCodeOrderItem|null
     */
    private function getCommodityCodeFromProduct($product): ?CommodityCodeOrderItem
    {
        $commodityCodeOrderItem = null;
        $commodityCodeProduct = $product->getExtensionAttributes()->getVertexCommodityCode();
        if ($commodityCodeProduct) {
            $commodityCodeOrderItem = $this->commodityCodeOrderItemFactory->create();
            $commodityCodeOrderItem->setType($commodityCodeProduct->getType());
            $commodityCodeOrderItem->setCode($commodityCodeProduct->getCode());
        }

        return $commodityCodeOrderItem;
    }
}
