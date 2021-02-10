<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem;

use Dotdigitalgroup\Sms\Model\Config\ConfigInterface;

class UpdateShipment extends AbstractOrderItem
{
    /**
     * @var int
     */
    protected $smsType = ConfigInterface::SMS_TYPE_UPDATE_SHIPMENT;

    /**
     * @var string
     */
    protected $smsConfigPath = ConfigInterface::XML_PATH_SMS_SHIPMENT_UPDATE_ENABLED;

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
