<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\Tax\Api\Data\CommodityCodeInterface;
use Vertex\Tax\Model\ResourceModel\CommodityCodeProduct as ResourceModel;

/**
 * Model for storage of the Vertex Commodity Code
 */
class CommodityCodeProduct extends AbstractModel implements CommodityCodeInterface
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
     * Get Product Id
     *
     * @return int
     */
    public function getProductId(): ?int
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
     * @return CommodityCodeInterface
     */
    public function setCode($code)
    {
        return $this->setData(static::FIELD_CODE, $code);
    }

    /**
     * Set product Id
     *
     * @param int $productId
     * @return CommodityCodeInterface
     */
    public function setProductId($productId)
    {
        return $this->setId($productId);
    }

    /**
     * Get commodity code type
     *
     * @param string $type
     * @return CommodityCodeInterface
     */
    public function setType($type)
    {
        return $this->setData(static::FIELD_TYPE, $type);
    }
}
