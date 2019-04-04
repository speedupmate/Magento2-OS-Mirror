<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\ResourceModel;

/**
 * Gift Message resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gift_message', 'gift_message_id');
    }
}
