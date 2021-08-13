<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Performs Datastore-related actions for the TaxCode repository
 */
class TaxCode extends AbstractDb
{
    const FIELD_ID = 'item_id';
    const FIELD_TAX_CODE = 'tax_code';
    const TABLE = 'vertex_sales_order_item_tax_code';

    /** @var ExceptionLogger */
    private $logger;

    /** @var null|string */
    private $table;

    /**
     * @param Context $context
     * @param ExceptionLogger $logger
     * @param null|string $table
     */
    public function __construct(Context $context, ExceptionLogger $logger, $table = self::TABLE)
    {
        $this->table = $table;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     *
     * MEQP2 Warning: Protected method.  Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init($this->table ?: self::TABLE, static::FIELD_ID);
    }

    /**
     * Retrieve rows from db by order item id array
     *
     * @param int[] $itemIdArray
     * @return string[]
     */
    public function getTaxCodeByItemIdArray(array $itemIdArray)
    {
        $returnArray = [];

        if (!count($itemIdArray)) {
            return $returnArray;
        }

        try {
            $select = $this->getConnection()
                ->select()
                ->from($this->getMainTable())
                ->where(self::FIELD_ID . ' IN (?)', $itemIdArray);

            foreach ($this->getConnection()->fetchAll($select) as $resultArray) {
                $returnArray[reset($resultArray)] = end($resultArray);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $returnArray;
    }

    /**
     * Store array of data to preferred attribute resource
     *
     * @param array $insertData
     * @return void
     */
    public function saveMultiple(array $insertData)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $processed = array_map('array_pop', $insertData);
            $connection->insertArray(
                $this->getTable($this->table ?: self::TABLE),
                [self::FIELD_ID, self::FIELD_TAX_CODE],
                $processed,
                AdapterInterface::INSERT_IGNORE
            );
            $connection->commit();
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $connection->rollBack();
        }
    }
}
