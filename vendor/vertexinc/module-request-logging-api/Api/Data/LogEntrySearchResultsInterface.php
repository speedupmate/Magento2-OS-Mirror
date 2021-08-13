<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLoggingApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Data model representing a result from a search against the Vertex API Log
 *
 * @api
 */
interface LogEntrySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get log entry list.
     *
     * @return LogEntryInterface[]
     */
    public function getItems(): array;

    /**
     * Set log entry list.
     *
     * @param LogEntryInterface[] $items
     * @return LogEntrySearchResultsInterface
     */
    public function setItems(array $items): self;
}
