<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Attribute\Mapping;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Model\Attribute\Mapping\ProductInterface;
use Temando\Shipping\Setup\SetupSchema;

/**
 * Temando Product Attribute Mapping Resource Model
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Product extends AbstractDb
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->logger = $logger;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
    }

    /**
     * Init main table and primary key.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SetupSchema::TABLE_PRODUCT_ATTRIBUTE_MAPPING, ProductInterface::NODE_PATH_ID);
    }

    /**
     * Create a new product attributes mapping.
     *
     * @param array $data
     * @return int
     * @throws LocalizedException
     */
    public function createNewProductAttributeMapping($data): int
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        return $connection->insertOnDuplicate($table, $data);
    }

    /**
     * Get the node path ID by mapped attribute ID
     *
     * @param $attributeId
     * @return string
     */
    public function getNodePathIdByMappedAttributeId($attributeId): string
    {
        try {
            $connection = $this->getConnection();
            $tableName = $this->getMainTable();
            $table = $this->getTable($tableName);

            $select = $connection->select()
                ->from($table, ProductInterface::NODE_PATH_ID)
                ->where('mapping_attribute_id = :attribute_id');

            $bind  = [
                ':attribute_id' => (string) $attributeId
            ];
            $nodePathId = $connection->fetchOne($select, $bind);

            return $nodePathId ? (string) $nodePathId : '';
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return '';
        }
    }

    /**
     * Is the product attribute already mapped to another node path ID
     *
     * @param $attributeId
     * @param $nodePathId
     * @return bool
     */
    public function isAttributeAlreadyMapped($attributeId, $nodePathId): bool
    {
        $foundNodePathId = $this->getNodePathIdByMappedAttributeId($attributeId);
        if ($foundNodePathId && $foundNodePathId !== $nodePathId) {
            return true;
        }

        return false;
    }

    /**
     * Gets all available mapped attributes
     *
     * @return array
     */
    public function getAllMappedAttributes(): array
    {
        try {
            $connection = $this->getConnection();
            $table  = $this->getMainTable();

            $select = $connection->select()
                ->from($table, [
                    ProductInterface::NODE_PATH_ID,
                    ProductInterface::MAPPED_ATTRIBUTE_ID,
                    ProductInterface::IS_DEFAULT
                ])
                ->where('mapping_attribute_id != :mapping');

            $bind = [':mapping' => ''];
            $entity = $connection->fetchAll($select, $bind);

            return $entity ?: [];
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return [];
        }
    }
}
