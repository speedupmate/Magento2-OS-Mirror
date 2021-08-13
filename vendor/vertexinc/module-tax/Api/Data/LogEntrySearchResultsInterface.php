<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Data model representing a result from a search against the Vertex API Log
 *
 * @api
 * @deprecated 4.2.1 Replaced by vertexinc/module-request-logging-api
 * @see \Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterface
 */
interface LogEntrySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get log entry list.
     *
     * @return \Vertex\Tax\Api\Data\LogEntryInterface[]
     */
    public function getItems();

    /**
     * Set log entry list.
     *
     * @param \Vertex\Tax\Api\Data\LogEntryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
