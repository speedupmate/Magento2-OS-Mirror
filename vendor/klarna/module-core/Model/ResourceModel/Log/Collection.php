<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Klarna\Core\Model\ResourceModel\Log as LogResourceModel;
use Klarna\Core\Model\Log as LogModel;

class Collection extends AbstractCollection
{
    /**
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(LogModel::class, LogResourceModel::class);
    }
}
