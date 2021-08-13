<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLoggingApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterface;

/**
 * Service Contract for retrieving, saving, and removing Vertex log entries
 *
 * @api
 */
interface LogEntryRepositoryInterface
{
    /**
     * Save a Vertex Log Entry
     *
     * @param LogEntryInterface $logEntry
     * @return LogEntryRepositoryInterface
     * @throws CouldNotSaveException
     */
    public function save(LogEntryInterface $logEntry): LogEntryRepositoryInterface;

    /**
     * Retrieve a collection of Vertex Log Entries based on the provided Search Criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LogEntrySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LogEntrySearchResultsInterface;

    /**
     * Delete a Vertex Log Entry
     *
     * @param LogEntryInterface $logEntry
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(LogEntryInterface $logEntry): bool;

    /**
     * Delete a Vertex Log Entry
     *
     * @param int $logEntryId
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $logEntryId): bool;

    /**
     * Delete multiple records by criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function deleteByCriteria(SearchCriteriaInterface $searchCriteria): bool;
}
