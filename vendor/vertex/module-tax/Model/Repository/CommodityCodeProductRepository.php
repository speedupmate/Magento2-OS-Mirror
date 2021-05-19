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
use Vertex\Tax\Model\Data\CommodityCodeProduct;
use Vertex\Tax\Model\Data\CommodityCodeProductFactory;
use Vertex\Tax\Model\ResourceModel\CommodityCodeProduct as ResourceModel;

/**
 * Repository of Vertex Commodity Codes
 */
class CommodityCodeProductRepository
{
    /** @var CommodityCodeProductFactory */
    private $factory;

    /** @var ResourceModel */
    private $resourceModel;

    public function __construct(ResourceModel $resourceModel, CommodityCodeProductFactory $factory)
    {
        $this->resourceModel = $resourceModel;
        $this->factory = $factory;
    }

    /**
     * Delete a Commodity Code
     *
     * @param CommodityCodeProduct $commodityCode
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CommodityCodeProduct $commodityCode)
    {
        try {
            $commodityCodeModel = $this->getDataModel($commodityCode);
            $this->resourceModel->delete($commodityCodeModel);
        } catch (Exception $originalException) {
            throw new CouldNotDeleteException(__('Unable to delete Commodity Code'), $originalException);
        }

        return true;
    }

    /**
     * Delete a Commodity Code given a Product ID
     *
     * @param int $productId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByProductId($productId)
    {
        try {
            $commodityCode = $this->getByProductId($productId);
            $this->delete($commodityCode);
        } catch (NoSuchEntityException $exception) {
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
     * @return CommodityCodeProduct
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var CommodityCodeProduct $commodityCode */
        $commodityCode = $this->factory->create();

        $this->resourceModel->load($commodityCode, $id, ResourceModel::FIELD_ID);
        if (!$commodityCode->getId()) {
            throw NoSuchEntityException::singleField('product_id', $id);
        }
        return $commodityCode;
    }

    /**
     * Retrieve a Commodity Code given a Product ID
     *
     * @param int $productId
     * @return CommodityCodeProduct
     * @throws NoSuchEntityException
     */
    public function getByProductId($productId)
    {
        return $this->get($productId);
    }

    /**
     * Retrieve an array of Commodity Code's indexed by Product ID
     *
     * @param int[] $productIds
     * @return CommodityCodeProduct[] Indexed by Product ID
     */
    public function getListByProductIds(array $productIds)
    {
        return $this->resourceModel->getArrayByProductIds($productIds);
    }

    /**
     * Save a Commodity Code
     *
     * @param CommodityCodeProduct $commodityCode
     * @return $this
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(CommodityCodeProduct $commodityCode)
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
     * @param CommodityCodeProduct $commodityCode
     * @return CommodityCodeProduct
     */
    private function getDataModel($commodityCode): CommodityCodeProduct
    {
        $model = $this->factory->create();

        $model->setData(
            [
                CommodityCodeProduct::FIELD_TYPE => $commodityCode->getType(),
                CommodityCodeProduct::FIELD_CODE => $commodityCode->getCode()
            ]
        );

        if ($commodityCode->getProductId() !== null) {
            $model->setData(CommodityCodeProduct::FIELD_ID, $commodityCode->getProductId());
        }

        return $model;
    }
}
