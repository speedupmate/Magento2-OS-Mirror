<?php
/**
 * Oder statuses grid collection
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Status;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Status\Collection
{
    /**
     * Join order states table
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinStates();
        return $this;
    }
}
