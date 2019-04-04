<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\ResourceModel\Debug;

/**
 * Resource Authorize.net debug collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Authorizenet\Model\Debug',
            'Magento\Authorizenet\Model\ResourceModel\Debug'
        );
    }
}
