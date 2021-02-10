<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;

class NewOrder extends AbstractOrderItem
{
    /**
     * @var int
     */
    protected $smsType = ConfigInterface::SMS_TYPE_NEW_ORDER;

    /**
     * @var string
     */
    protected $smsConfigPath  = ConfigInterface::XML_PATH_SMS_NEW_ORDER_ENABLED;

    /**
     * @param $order
     */
    public function buildAdditionalData($order)
    {
        $this->order = $order;
        $this->additionalData->orderStatus = $order->getStatus();
        return $this;
    }
}
