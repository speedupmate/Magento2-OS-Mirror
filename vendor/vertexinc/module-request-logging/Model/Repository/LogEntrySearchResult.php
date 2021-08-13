<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\Repository;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterface;

/**
 * Search result implementation for repository lookup.
 *
 * Methods duplicated from SearchResults and AbstractSimpleObject as they are not in the public API
 */
class LogEntrySearchResult implements LogEntrySearchResultsInterface
{
    const KEY_ITEMS = 'items';
    const KEY_SEARCH_CRITERIA = 'search_criteria';
    const KEY_TOTAL_COUNT = 'total_count';

    /**
     * @var array
     */
    private $data;

    /**
     * Initialize internal storage
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     */
    private function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set value for the given key
     *
     * @param string $key
     * @param mixed $value
     * @return LogEntrySearchResult
     */
    private function setData(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get items
     *
     * @return AbstractExtensibleObject[]
     */
    public function getItems(): array
    {
        return $this->getData(self::KEY_ITEMS) === null ? [] : $this->getData(self::KEY_ITEMS);
    }

    /**
     * Set items
     *
     * @param AbstractExtensibleObject[] $items
     * @return LogEntrySearchResultsInterface
     */
    public function setItems(array $items): LogEntrySearchResultsInterface
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * Get search criteria
     *
     * @return null|SearchCriteria
     */
    public function getSearchCriteria(): ?SearchCriteria
    {
        return $this->getData(self::KEY_SEARCH_CRITERIA);
    }

    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LogEntrySearchResult
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria): self
    {
        return $this->setData(self::KEY_SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * Get total count
     *
     * @return null|int
     */
    public function getTotalCount(): ?int
    {
        return $this->getData(self::KEY_TOTAL_COUNT);
    }

    /**
     * Set total count
     *
     * @param int $count
     * @return LogEntrySearchResult
     */
    public function setTotalCount($count): LogEntrySearchResult
    {
        return $this->setData(self::KEY_TOTAL_COUNT, $count);
    }
}
