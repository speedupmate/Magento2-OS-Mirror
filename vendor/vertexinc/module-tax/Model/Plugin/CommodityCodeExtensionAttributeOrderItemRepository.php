<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CommodityCodeOrderItem;
use Vertex\Tax\Model\Data\CommodityCodeOrderItemFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\CommodityCodeOrderItemRepository;

/**
 * Add Commodity Code extension attribute to Order Item repository
 *
 * @see OrderItemRepositoryInterface
 */
class CommodityCodeExtensionAttributeOrderItemRepository
{
    /** @var Config */
    private $config;

    /** @var CommodityCodeOrderItemRepository */
    private $commodityCodeOrderItemRepository;

    /** @var CommodityCodeOrderItemFactory */
    private $commodityCodeOrderItemFactory;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        Config $config,
        CommodityCodeOrderItemRepository $commodityCodeOrderItemRepository,
        CommodityCodeOrderItemFactory $commodityCodeOrderItemFactory,
        ExceptionLogger $logger
    ) {
        $this->config = $config;
        $this->commodityCodeOrderItemRepository = $commodityCodeOrderItemRepository;
        $this->commodityCodeOrderItemFactory = $commodityCodeOrderItemFactory;
        $this->logger = $logger;
    }

    /**
     * Delete the Commodity Code when the Order Item is deleted
     *
     * @see OrderItemRepositoryInterface::delete()
     * @param OrderItemRepositoryInterface $subject
     * @param bool $result
     * @param OrderItemInterface $orderItem
     * @return bool
     */
    public function afterDelete(
        OrderItemRepositoryInterface $subject,
        $result,
        OrderItemInterface $orderItem
    ): bool {
        if ($orderItem->getItemId() && $result) {
            $this->deleteByOrderItemId($orderItem->getItemId());
        }

        return $result;
    }

    /**
     * Add Commodity Code to the Order Item extension attribute when an Order Item is retrieved
     *
     * @see OrderItemRepositoryInterface::get()
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $result
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderItemRepositoryInterface $subject,
        OrderItemInterface $result
    ): OrderItemInterface {
        if (!$this->config->isVertexActive()) {
            return $result;
        }

        try {
            $commodityCode = $this->commodityCodeOrderItemRepository->getByOrderItemId($result->getItemId());
            $result->getExtensionAttributes()->setVertexCommodityCode($commodityCode);
        } catch (NoSuchEntityException $exception) {
            /* No-op */
            return $result;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Add Vat Country Code to the Order Address extension attribute when an Order Address is retrieved
     *
     * @see OrderItemRepositoryInterface::getList()
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $results
     * @return OrderItemSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, $results): OrderItemSearchResultInterface
    {
        if (!$this->config->isVertexActive() || $results->getTotalCount() <= 0) {
            return $results;
        }

        $itemIds = array_map(
            static function (OrderItemInterface $orderItem) {
                return $orderItem->getItemId();
            },
            $results->getItems()
        );

        $commodityCodes = $this->commodityCodeOrderItemRepository->getListByOrderItemIds($itemIds);

        foreach ($results->getItems() as $orderItem) {
            if (!isset($commodityCodes[$orderItem->getItemId()])) {
                continue;
            }

            $extensionAttributes = $orderItem->getExtensionAttributes();
            $extensionAttributes->setVertexCommodityCode(
                $commodityCodes[$orderItem->getItemId()]
            );
        }

        return $results;
    }

    /**
     * Save Commodity Code extension attribute
     *
     * @see OrderItemRepositoryInterface::save()
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $result The order address entity result.
     * @param OrderItemInterface $orderItem The order address entity with modified data, if any.
     * @return OrderItemInterface
     */
    public function afterSave(
        OrderItemRepositoryInterface $subject,
        OrderItemInterface $result,
        OrderItemInterface $orderItem
    ): OrderItemInterface {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        if ($orderItem->getExtensionAttributes()) {
            $commodityCode = $orderItem->getExtensionAttributes()->getVertexCommodityCode();

            if ($commodityCode) {
                $commodityCodeOrderItem = $this->getCommodityCodeOrderItemModel($result->getItemId());
                $commodityCodeOrderItem->setCode($commodityCode->getCode());
                $commodityCodeOrderItem->setType($commodityCode->getType());

                try {
                    $this->commodityCodeOrderItemRepository->save($commodityCodeOrderItem);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                $this->deleteByOrderItemId($result->getItemId());
            }
        }

        return $result;
    }

    /**
     * Delete a Commodity Code given a OrderItem Id
     *
     * @param int $itemId
     * @return void
     */
    private function deleteByOrderItemId($itemId)
    {
        try {
            $this->commodityCodeOrderItemRepository->deleteByOrderItemId($itemId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve the Commodity Code by Order Item Id
     *
     * @param int $itemId
     * @return CommodityCodeOrderItem
     */
    private function getCommodityCodeOrderItemModel($itemId): CommodityCodeOrderItem
    {
        try {
            $commodityCode = $this->commodityCodeOrderItemRepository->getByOrderItemId($itemId);
        } catch (NoSuchEntityException $e) {
            /** @var CommodityCodeOrderItem $commodityCode */
            $commodityCode = $this->commodityCodeOrderItemFactory->create();
            $commodityCode->setOrderItemId($itemId);
        }
        return $commodityCode;
    }
}
