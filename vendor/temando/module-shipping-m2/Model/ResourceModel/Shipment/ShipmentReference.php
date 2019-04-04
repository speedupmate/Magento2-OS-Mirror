<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Shipment;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Setup\SetupSchema;

/**
 * Temando Shipment Resource Model
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class ShipmentReference extends AbstractDb
{
    /**
     * Init main table and primary key.
     *
     * @return void
     */
    protected function _construct()
    {
         $this->_init(SetupSchema::TABLE_SHIPMENT, ShipmentReferenceInterface::ENTITY_ID);
    }

    /**
     * Query primary key by given shipment id.
     *
     * @param int $shipmentId
     * @return int|null
     */
    public function getIdByShipmentId($shipmentId)
    {
        try {
            $connection = $this->getConnection();
            $tableName  = $this->getMainTable();
            $table      = $this->getTable($tableName);

            $select = $connection->select()
                ->from($table, ShipmentReferenceInterface::ENTITY_ID)
                ->where('shipment_id = :shipment_id');

            $bind  = [':shipment_id' => (string)$shipmentId];
            $entityId = $connection->fetchOne($select, $bind);

            return $entityId ? (int) $entityId : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Query primary key by given platform shipment id.
     *
     * @param string $shipmentId
     * @return int|null
     */
    public function getIdByExtShipmentId($shipmentId)
    {
        try {
            $connection = $this->getConnection();
            $tableName  = $this->getMainTable();
            $table      = $this->getTable($tableName);

            $select = $connection->select()
                ->from($table, ShipmentReferenceInterface::ENTITY_ID)
                ->where('ext_shipment_id = :ext_shipment_id');

            $bind = [':ext_shipment_id' => (string)$shipmentId];
            $entityId = $connection->fetchOne($select, $bind);

            return $entityId ? (int) $entityId : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Query primary key by given platform return shipment id.
     *
     * @param string $returnShipmentId
     * @return int|null
     */
    public function getIdByExtReturnShipmentId($returnShipmentId)
    {
        try {
            $connection = $this->getConnection();
            $table = $this->getMainTable();

            $select = $connection->select()
                ->from($table, ShipmentReferenceInterface::ENTITY_ID)
                ->where('ext_return_shipment_id = :ext_return_shipment_id');

            $bind = [':ext_return_shipment_id' => (string)$returnShipmentId];
            $entityId = $connection->fetchOne($select, $bind);

            return $entityId ? (int) $entityId : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Query multiple primary keys by given platform shipment ids.
     *
     * @param string[] $extShipmentIds
     * @return int[]
     */
    public function getShipmentIdsByExtShipmentIds(array $extShipmentIds)
    {
        try {
            $connection = $this->getConnection();
            $tableName  = $this->getMainTable();
            $table      = $this->getTable($tableName);

            $bind = [];
            foreach ($extShipmentIds as $index => $extShipmentId) {
                $bind[":id{$index}"] = $extShipmentId;
            }

            $select = $connection->select()
                ->from($table, [ShipmentReferenceInterface::EXT_SHIPMENT_ID, ShipmentReferenceInterface::SHIPMENT_ID])
                ->where('ext_shipment_id in (' . implode(',', array_keys($bind)) . ')');

            return $connection->fetchPairs($select, $bind);
        } catch (\Exception $exception) {
            return [];
        }
    }
}
