<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;

class NewShipment extends AbstractOrderItem
{
    /**
     * @var int
     */
    protected $smsConfigPath = ConfigInterface::XML_PATH_SMS_NEW_SHIPMENT_ENABLED;

    /**
     * @var int
     */
    protected $smsType = ConfigInterface::SMS_TYPE_NEW_SHIPMENT;

    /**
     * @param $order
     * @param $trackingNumber
     * @param $carrierCode
     */
    public function buildAdditionalData($order, $trackingNumber, $carrierCode)
    {
        $this->order = $order;

        $this->additionalData->orderStatus = $order->getStatus();
        $this->additionalData->trackingNumber = $trackingNumber;
        $this->additionalData->trackingCarrier = $carrierCode;

        return $this;
    }
}
