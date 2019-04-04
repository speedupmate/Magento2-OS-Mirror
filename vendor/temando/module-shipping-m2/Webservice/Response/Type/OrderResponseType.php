<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Webservice\Response\Type;

use Temando\Shipping\Model\Shipment\ShipmentErrorInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Order Save Result
 *
 * @package Temando\Shipping\Webservice
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderResponseType
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var ShipmentErrorInterface[]
     */
    private $errors;

    /**
     * @var ShipmentInterface[]
     */
    private $shipments;

    /**
     * OrderResponseType constructor.
     * @param string $orderId
     * @param ShipmentErrorInterface[] $errors
     * @param ShipmentInterface[] $shipments
     */
    public function __construct(
        $orderId,
        array $errors = [],
        array $shipments = []
    ) {
        $this->orderId = $orderId;
        $this->errors = $errors;
        $this->shipments = $shipments;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return ShipmentErrorInterface[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return ShipmentInterface[]
     */
    public function getShipments()
    {
        return $this->shipments;
    }
}
