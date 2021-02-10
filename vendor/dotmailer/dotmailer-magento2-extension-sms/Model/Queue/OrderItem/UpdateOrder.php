<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;

class UpdateOrder extends AbstractOrderItem
{
    /**
     * @var string
     */
    protected $smsConfigPath = ConfigInterface::XML_PATH_SMS_ORDER_UPDATE_ENABLED;

    /**
     * @var int
     */
    protected $smsType = ConfigInterface::SMS_TYPE_UPDATE_ORDER;

    /**
     * @param $order
     * @return $this
     */
    public function buildAdditionalData($order)
    {
        $this->order = $order;
        $this->additionalData->orderStatus = $order->getStatus();
        return $this;
    }
}
