<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    /**
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('klarna_logs', 'log_id');
    }
}
