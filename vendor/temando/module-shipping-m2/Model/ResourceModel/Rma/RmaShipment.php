<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\ResourceModel\Rma;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Setup\RmaSetupSchema;

/**
 * The RMA Shipment Resource Model grants access to the RMA-Shipment associations.
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class RmaShipment extends AbstractDb
{
    const RMA_ID = 'rma_id';
    const RMA_SHIPMENT_ID = 'ext_shipment_id';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RmaShipment constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setMainTable(RmaSetupSchema::TABLE_RMA_SHIPMENT);
    }

    /**
     * @param AbstractModel $object
     * @param int $value
     * @param string|null $field
     *
     * @return $this
     * @throws LocalizedException
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!$field || $field === self::RMA_SHIPMENT_ID) {
            // query RMA ID for given shipment
            $connection = $this->getConnection();
            $table = $this->getMainTable();

            $select = $connection
                ->select()
                ->from($table, self::RMA_ID)
                ->where(self::RMA_SHIPMENT_ID . ' = ?', $value);

            $rmaId = $connection->fetchOne($select);

            $object->setData([
                self::RMA_ID => $rmaId,
                self::RMA_SHIPMENT_ID => $value,
            ]);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $rmaId = $object->getData(self::RMA_ID);
        $shipmentId = $object->getData(self::RMA_SHIPMENT_ID);

        $this->saveShipmentIds($rmaId, [$shipmentId]);

        return $this;
    }

    /**
     * Query all external shipment IDs associated to the given RMA ID.
     *
     * @param int $rmaId
     *
     * @return string[]
     * @throws \Exception
     */
    public function getShipmentIds($rmaId)
    {
        try {
            $connection = $this->getConnection();
            $table = $this->getMainTable();

            $select = $connection
                ->select()
                ->from($table, self::RMA_SHIPMENT_ID)
                ->where(self::RMA_ID . ' = ?', $rmaId);

            return $connection->fetchCol($select);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param int $rmaId
     * @param string[] $shipmentIds
     * @return int Number of saved entries
     * @throws \Exception
     */
    public function saveShipmentIds($rmaId, array $shipmentIds)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $data = array_map(function ($shipmentId) use ($rmaId) {
            return [
                self::RMA_ID => $rmaId,
                self::RMA_SHIPMENT_ID => $shipmentId,
            ];
        }, $shipmentIds);

        return $connection->insertOnDuplicate($table, $data);
    }

    /**
     * @param int $rmaId
     * @param string[] $shipmentIds
     * @return int Number of dropped entries
     * @throws \Exception
     */
    public function deleteShipmentIds($rmaId, array $shipmentIds)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $conditions = [
            sprintf("`%s` = ?", self::RMA_ID),
            sprintf("`%s` in (?)", self::RMA_SHIPMENT_ID),
        ];
        $terms = [
            $rmaId,
            $shipmentIds,
        ];

        $where = array_combine($conditions, $terms);

        return $connection->delete($table, $where);
    }

    /**
     * Query primary key by given platform shipment id.
     *
     * @param string $extShipmentId
     * @return int|null
     */
    public function getIdByExtShipmentId($extShipmentId)
    {
        try {
            $connection = $this->getConnection();
            $tableName = $this->getMainTable();
            $table = $this->getTable($tableName);

            $select = $connection->select()
                                 ->from($table, self::RMA_ID)
                                 ->where('ext_shipment_id = :ext_shipment_id');

            $bind = [':ext_shipment_id' => (string)$extShipmentId];
            $entityId = $connection->fetchOne($select, $bind);

            return $entityId ? (int)$entityId : null;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return null;
        }
    }
}
