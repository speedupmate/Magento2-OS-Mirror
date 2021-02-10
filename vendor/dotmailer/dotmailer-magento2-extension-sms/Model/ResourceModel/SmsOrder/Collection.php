<?php

namespace Dotdigitalgroup\Sms\Model\ResourceModel\SmsOrder;

use Dotdigitalgroup\Sms\Model\ResourceModel\SmsOrder as SmsOrderResource;
use Dotdigitalgroup\Sms\Model\SmsOrder as SmsOrderModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * SmsOrder Initialization
     */
    public function _construct()
    {
        $this->_init(
            SmsOrderModel::class,
            SmsOrderResource::class
        );
    }
}
