<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface as SalesShipmentInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Provider Interface.
 *
 * Provide a shipment entity fetched from the platform for re-use in the current
 * request cycle.
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ShipmentProviderInterface
{
    /**
     * @return ShipmentInterface
     */
    public function getShipment(): ?ShipmentInterface;

    /**
     * @param ShipmentInterface $shipment
     *
     * @return void
     */
    public function setShipment(ShipmentInterface $shipment): void;

    /**
     * @return SalesShipmentInterface
     */
    public function getSalesShipment(): ?SalesShipmentInterface;

    /**
     * @param SalesShipmentInterface $shipment
     *
     * @return void
     */
    public function setSalesShipment(SalesShipmentInterface $shipment): void;
}
