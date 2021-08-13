<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vertex\Tax\Model\Data\CommodityCodeOrderItem as Model;
use Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem as ResourceModel;

/**
 * Collection of Commodity Codes
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     *
     * MEQP2 Warning: Protected method.  Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
