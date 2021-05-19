<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem\Collection;
use Vertex\Tax\Model\ResourceModel\CommodityCodeOrderItem\CollectionFactory;

/**
 * Performs Datastore-related actions for the CommodityCodeOrderItem repository
 */
class CommodityCodeOrderItem extends AbstractDb
{
    const TABLE = 'vertex_commodity_code_order_item';

    const FIELD_ID = 'order_item_id';
    const FIELD_CODE = 'code';
    const FIELD_TYPE = 'type';

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param string|null $connectionName
     */
    public function __construct(Context $context, CollectionFactory $collectionFactory, $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     *
     * MEQP2 Warning: Protected method.  Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init(static::TABLE, static::FIELD_ID);
    }

    /**
     * Retrieve a list of Commodity Codes indexed by OrderItem ID
     *
     * @param int[] $itemIds
     * @return \Vertex\Tax\Model\Data\CommodityCodeOrderItem[]
     */
    public function getArrayByOrderItemIds($itemIds)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::FIELD_ID, ['in' => $itemIds]);
        $collection->load();

        $result = [];
        foreach ($collection->getItems() as $item) {
            /** @var \Vertex\Tax\Model\Data\CommodityCodeOrderItem $item */
            $result[$item->getOrderItemId()] = $item;
        }

        return $result;
    }
}
