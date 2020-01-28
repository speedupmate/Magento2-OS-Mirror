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
use Vertex\Tax\Model\Data\CustomOptionFlexibleField as DataModel;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField;

/**
 * Repository for handling the loading and caching of Custom Option Flexible Field mappings
 */
class CustomOptionFlexibleFieldRepository
{
    /** @var array */
    private $optionIdCache = [];

    /** @var CustomOptionFlexibleField */
    private $resource;

    /**
     * @param CustomOptionFlexibleField $resource
     */
    public function __construct(CustomOptionFlexibleField $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Save a mapping
     *
     * @param DataModel $mapping
     * @return CustomOptionFlexibleField
     * @throws CouldNotSaveException
     */
    public function save(DataModel $mapping)
    {
        try {
            $result = $this->resource->save($mapping);
            $this->optionIdCache[$mapping->getWebsiteId()][$mapping->getOptionId()] = $result;
            return $result;
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save Custom Option Flexible Field Mapping'), $e);
        }
    }

    /**
     * Delete a mapping
     *
     * @param DataModel $mapping
     * @throws CouldNotDeleteException
     */
    public function delete(DataModel $mapping)
    {
        try {
            $optionId = $mapping->getOptionId();
            $websiteId = $mapping->getWebsiteId();
            $this->resource->delete($mapping);
            if ($websiteId == 0) {
                $this->clearOptionFromCache($optionId);
            } else {
                unset($this->optionIdCache[$websiteId][$optionId]);
            }
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete Custom Option Flexible Field Mapping'), $e);
        }
    }

    /**
     * Delete a mapping by Option ID
     *
     * @param int $optionId
     * @return void
     */
    public function deleteByOptionId($optionId)
    {
        $this->resource->deleteByOptionId($optionId);
        $this->clearOptionFromCache($optionId);
    }

    /**
     * Retrieve mappings for a list of option IDs
     *
     * @param int[] $optionIds
     * @param int $websiteId
     * @return DataModel[] Custom Option Mappings indexed by Option ID
     */
    public function getListForOptions(array $optionIds, $websiteId = 0): array
    {
        if (!$this->allExistInCache($optionIds, $websiteId)) {
            $this->fillCache(
                $optionIds,
                $websiteId
            );
        }

        return array_reduce(
            $optionIds,
            function (array $result, $optionId) use ($websiteId) {
                $result[$optionId] = $this->optionIdCache[$websiteId][$optionId];
                return $result;
            },
            []
        );
    }

    /**
     * Retrieve a mapping by the Option ID
     *
     * @param int $optionId
     * @param int $websiteId
     * @param bool $includeDefaultScope
     * @return DataModel
     * @throws NoSuchEntityException
     */
    public function getForOptionId($optionId, $websiteId, $includeDefaultScope = false): DataModel
    {
        return $this->resource->loadByOptionId($optionId, $websiteId, $includeDefaultScope);
    }

    /**
     * Check that all option IDs for a given website exist in the cache
     *
     * @param int[] $optionIds
     * @param int $websiteId
     * @return bool
     */
    private function allExistInCache(array $optionIds, $websiteId): bool
    {
        if (!array_key_exists($websiteId, $this->optionIdCache)) {
            // Scope has not been looked up
            return false;
        }

        foreach ($optionIds as $optionId) {
            if (!array_key_exists($optionId, $this->optionIdCache[$websiteId])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete all results for an Option ID from the cache
     *
     * @param int $optionId
     * @return void
     */
    private function clearOptionFromCache($optionId)
    {
        foreach ($this->optionIdCache as $websiteId => $cache) {
            unset($this->optionIdCache[$websiteId][$optionId]);
        }
    }

    /**
     * Load mappings into the cache
     *
     * @param int[] $optionIds
     * @param int $websiteId
     * @return DataModel[] Custom Option Mappings indexed by Option ID
     */
    private function fillCache(array $optionIds, $websiteId): array
    {
        $results = $this->resource->loadForOptions($optionIds, $websiteId);

        array_walk(
            $optionIds,
            function ($optionId) use ($results, $websiteId) {
                $this->optionIdCache[$websiteId][$optionId] = $results[$optionId] ?? null;
            }
        );

        return $results;
    }
}
