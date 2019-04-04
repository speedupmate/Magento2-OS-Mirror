<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Performs Datastore-related actions for the LogEntry repository
 */
class LogEntry extends AbstractDb
{
    /**
     * @inheritdoc
     *
     * MEQP2 Warning: Protected method.  Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_init('vertex_taxrequest', 'request_id');
    }

    /**
     * Delete records in a table based on the collection passed in
     *
     * @param LogEntry\Collection $collection
     * @throws CouldNotDeleteException
     */
    public function deleteByCollection($collection)
    {
        $query = $collection->getSelect()->deleteFromSelect('main_table');

        try {
            $this->getConnection()->query($query);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('%1 could not delete log entries', __CLASS__), $e);
        }
    }
}
