<?php

namespace Dotdigitalgroup\Sms\Model\Queue\OrderItem\Data;

class ShipmentData extends OrderData
{
    /**
     * @var string
     */
    public $trackingNumber;

    /**
     * @var string
     */
    public $trackingCarrier;
}
