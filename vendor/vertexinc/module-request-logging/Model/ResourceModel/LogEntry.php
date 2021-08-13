<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry\Collection;

/**
 * Performs Datastore-related actions for the LogEntry repository
 */
class LogEntry extends AbstractDb
{
    /**
     * MEQP2 Warning: Protected method.  Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_init('vertex_taxrequest', 'request_id');
    }

    /**
     * Delete records in a table based on the collection passed in
     *
     * @param Collection $collection
     * @throws CouldNotDeleteException
     */
    public function deleteByCollection(Collection $collection): void
    {
        $query = $collection->getSelect()->deleteFromSelect('main_table');

        try {
            $this->getConnection()->query($query);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('%1 could not delete log entries', __CLASS__), $e);
        }
    }
}
