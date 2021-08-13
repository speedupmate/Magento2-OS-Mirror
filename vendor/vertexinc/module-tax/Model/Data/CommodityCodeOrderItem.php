<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\Tax\Api\Data\CommodityCodeInterface;
use Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem as ResourceModel;
use Vertex\Tax\Ui\DataProvider\Product\Form\Modifier\CommodityCode;

/**
 * Model for storage of the Vertex Commodity Code
 */
class CommodityCodeOrderItem extends AbstractModel implements CommodityCodeInterface
{
    const FIELD_ID = ResourceModel::FIELD_ID;
    const FIELD_CODE = ResourceModel::FIELD_CODE;
    const FIELD_TYPE = ResourceModel::FIELD_TYPE;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get commodity code
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->getData(static::FIELD_CODE);
    }

    /**
     * Get OrderItem Id
     *
     * @return null|int
     */
    public function getOrderItemId(): ?int
    {
        $id = $this->getId();
        return $id ? (int) $id : null;
    }

    /**
     * Get commodity code type
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getData(static::FIELD_TYPE);
    }

    /**
     * Set commodity code
     *
     * @param string $code
     * @return CommodityCodeOrderItem
     */
    public function setCode($code): CommodityCodeOrderItem
    {
        return $this->setData(static::FIELD_CODE, $code);
    }

    /**
     * Set OrderItem Id
     *
     * @param int $id
     * @return CommodityCodeOrderItem
     */
    public function setOrderItemId($id): CommodityCodeOrderItem
    {
        return $this->setId($id);
    }

    /**
     * Get commodity code type
     *
     * @param string $type
     * @return CommodityCodeOrderItem
     */
    public function setType($type): CommodityCodeOrderItem
    {
        return $this->setData(static::FIELD_TYPE, $type);
    }
}
