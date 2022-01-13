<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Vertex\Tax\Model\Data\CustomOptionFlexibleField as DataModel;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField\Collection;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField\CollectionFactory;
use Zend_Db_Expr;

/**
 * Resource Model for Custom Option Flexible Field mappings
 */
class CustomOptionFlexibleField extends AbstractDb
{
    const FIELD_FLEX_FIELD_IDENTIFIER = 'flex_field';
    const FIELD_ID = 'entity_id';
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_WEBSITE_ID = 'website_id';
    const TABLE = 'vertex_custom_option_flex_field';

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param string|null $connectionName
     */
    public function __construct(Context $context, CollectionFactory $collectionFactory, $connectionName = null)
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     *
     */
    protected function _construct()
    {
        $this->_init(static::TABLE, static::FIELD_ID);
    }

    /**
     * Delete all flexible field mapping objects for an Option ID
     *
     * @param int $optionId
     * @return bool Whether or not any were deleted.
     */
    public function deleteByOptionId($optionId): bool
    {
        $db = $this->getConnection();
        return $db->delete(
            $this->_resources->getTableName(static::TABLE),
            [static::FIELD_OPTION_ID . ' = ?' => $optionId]
        ) > 0;
    }

    /**
     * Duplicate options from one Option ID to another
     *
     * @param int $originalOptionId
     * @param int $duplicateOptionId
     * @return void
     */
    public function duplicate($originalOptionId, $duplicateOptionId)
    {
        // INSERT INTO vertex_custom_option_flex_field (option_id, website_id, flex_field)
        // SELECT :duplicateOptionId, website_id, flex_field FROM vertex_custom_option_flex_field
        //   WHERE option_id = :originalOptionId

        $duplicateOptionId = (int)$duplicateOptionId;
        $db = $this->getConnection();
        $select = $db->select()
            ->from(
                $this->_resources->getTableName(static::TABLE),
                [
                    new Zend_Db_Expr($duplicateOptionId), // Unescaped b/c we typecast to int
                    static::FIELD_WEBSITE_ID,
                    static::FIELD_FLEX_FIELD_IDENTIFIER
                ]
            )
            ->where(static::FIELD_OPTION_ID . ' = ?', $originalOptionId);

        $insert = $db->insertFromSelect(
            $select,
            $this->_resources->getTableName(static::TABLE),
            [
                static::FIELD_OPTION_ID,
                static::FIELD_WEBSITE_ID,
                static::FIELD_FLEX_FIELD_IDENTIFIER
            ]
        );
        $db->query($insert);
    }

    /**
     * Retrieve a flexible field mapping object by Option ID
     *
     * @param int $optionId
     * @param int $websiteId
     * @param bool $addDefault Set to false to only return an entry for the provided website ID
     * @return DataModel
     * @throws NoSuchEntityException
     */
    public function loadByOptionId($optionId, $websiteId = 0, $addDefault = true): DataModel
    {
        $collection = $this->getCollectionForOptionIds([$optionId], $websiteId, $addDefault);
        // Sorting by descending will ensure a website-specific code is the first item
        $collection->addOrder(static::FIELD_WEBSITE_ID, 'DESC');
        $collection->load();

        if ($collection->getSize() < 1) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }

        return $collection->getFirstItem();
    }

    /**
     * Retrieve flexible field mapping objects by Option ID
     *
     * @param int[] $optionIds
     * @param int|int[] $websiteId
     * @param bool $addDefault Set to false to only return an entry for the provided website ID
     * @return DataModel[] Empty if no results are found, Indexed by Option ID
     */
    public function loadForOptions(array $optionIds, $websiteId = 0, $addDefault = true): array
    {
        $collection = $this->getCollectionForOptionIds($optionIds, $websiteId, $addDefault);
        // Sorting by ascending will ensure that the website-specific code is the final one used in the array
        $collection->addOrder(static::FIELD_WEBSITE_ID, 'ASC');

        return array_reduce(
            $collection->getItems(),
            static function (array $result, DataModel $item) {
                $result[$item->getOptionId()] = $item;
                return $result;
            },
            []
        );
    }

    /**
     * Create a collection for loading flexible field mapping objects via Option ID
     *
     * @param int[] $optionIds
     * @param int|int[] $websiteIds
     * @param bool $addDefault Set to false to only return an entry for the provided website ID
     * @return Collection
     */
    private function getCollectionForOptionIds(array $optionIds, $websiteIds = 0, $addDefault = true): Collection
    {
        if (!is_array($websiteIds)) {
            $websiteIds = [$websiteIds];
        }
        if ($addDefault && !in_array(0, $websiteIds, true)) {
            $websiteIds[] = 0;
        }

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::FIELD_OPTION_ID, ['in' => $optionIds]);
        $collection->addFieldToFilter(static::FIELD_WEBSITE_ID, ['in' => $websiteIds]);

        return $collection;
    }
}
