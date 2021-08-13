<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\ResourceModel\LogEntry;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vertex\RequestLoggingApi\Model\Data\LogEntry as Model;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry as ResourceModel;

/**
 * Collection of Log Entries
 */
class Collection extends AbstractCollection
{
    /**
     * MEQP2 Warning: Protected method. Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
