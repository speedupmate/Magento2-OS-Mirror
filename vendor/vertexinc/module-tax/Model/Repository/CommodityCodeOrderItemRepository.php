<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Repository;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\Data\CommodityCodeOrderItem;
use Vertex\Tax\Model\Data\CommodityCodeOrderItemFactory;
use Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem as ResourceModel;

/**
 * Repository of Vertex Commodity Codes
 */
class CommodityCodeOrderItemRepository
{
    /** @var CommodityCodeOrderItemFactory */
    private $factory;

    /** @var ResourceModel */
    private $resourceModel;

    public function __construct(ResourceModel $resourceModel, CommodityCodeOrderItemFactory $factory)
    {
        $this->resourceModel = $resourceModel;
        $this->factory = $factory;
    }

    /**
     * Delete a Commodity Code
     *
     * @param CommodityCodeOrderItem $commodityCode
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CommodityCodeOrderItem $commodityCode)
    {
        try {
            $this->resourceModel->delete($commodityCode);
        } catch (Exception $originalException) {
            throw new CouldNotDeleteException(__('Unable to delete Commodity Code'), $originalException);
        }

        return true;
    }

    /**
     * Delete a Commodity Code given an OrderItem ID
     *
     * @param int $itemId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByOrderItemId($itemId)
    {
        try {
            $commodityCode = $this->getByOrderItemId($itemId);
            $this->delete($commodityCode);
        } catch (NoSuchEntityException $exception) {
            /* No-op */
            return false;
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__('Unable to delete Commodity Code'), $exception);
        }

        return true;
    }

    /**
     * Retrieve a Commodity Code by Id
     *
     * @param int $id
     * @return CommodityCodeOrderItem
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var CommodityCodeOrderItem $commodityCode */
        $commodityCode = $this->factory->create();

        $this->resourceModel->load($commodityCode, $id);
        if (!$commodityCode->getId()) {
            throw NoSuchEntityException::singleField('itemId', $id);
        }
        return $commodityCode;
    }

    /**
     * Retrieve a Commodity Code given an OrderItem ID
     *
     * @param int $itemId
     * @return CommodityCodeOrderItem
     * @throws NoSuchEntityException
     */
    public function getByOrderItemId($itemId)
    {
        return $this->get($itemId);
    }

    /**
     * Retrieve an array of Commodity Codes indexed by OrderItem ID
     *
     * @param int[] $itemIds
     * @return CommodityCodeOrderItem[] Indexed by OrderItem ID
     */
    public function getListByOrderItemIds(array $itemIds)
    {
        return $this->resourceModel->getArrayByOrderItemIds($itemIds);
    }

    /**
     * Save a Commodity Code
     *
     * @param CommodityCodeOrderItem $commodityCode
     * @return $this
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(CommodityCodeOrderItem $commodityCode)
    {
        try {
            $commodityCodeModel = $this->getDataModel($commodityCode);
            $this->resourceModel->save($commodityCodeModel);
        } catch (AlreadyExistsException $e) {
            throw $e;
        } catch (Exception $originalException) {
            throw new CouldNotSaveException(__('Unable to save Commodity Code'), $originalException);
        }
        return $this;
    }

    /**
     * Get data model
     *
     * @param CommodityCodeOrderItem $commodityCode
     * @return CommodityCodeOrderItem
     */
    private function getDataModel($commodityCode): CommodityCodeOrderItem
    {
        $model = $this->factory->create();

        $model->setData(
            [
                CommodityCodeOrderItem::FIELD_TYPE => $commodityCode->getType(),
                CommodityCodeOrderItem::FIELD_CODE => $commodityCode->getCode()
            ]
        );

        if ($commodityCode->getOrderItemId() !== null) {
            $model->setData(CommodityCodeOrderItem::FIELD_ID, $commodityCode->getOrderItemId());
        }

        return $model;
    }
}
