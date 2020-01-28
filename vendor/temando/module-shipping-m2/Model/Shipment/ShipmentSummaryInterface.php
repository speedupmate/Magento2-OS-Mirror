<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Shipment;

/**
 * Temando Shipment Summary Interface.
 *
 * As opposed to the standalone shipment entity, the shipment summary contains
 * only a subset of the shipment's data and is usually obtained as a member
 * property of a primary entity, e.g. a Batch.
 *
 * @see \Temando\Shipping\Model\BatchInterface::getIncludedShipments()
 * @see \Temando\Shipping\Model\BatchInterface::getFailedShipments()
 * @see \Temando\Shipping\Model\ShipmentInterface
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ShipmentSummaryInterface
{
    const ORDER_ID = 'order_id';
    const SHIPMENT_ID = 'shipment_id';
    const STATUS = 'status';
    const ITEMS = 'items';
    const DESTINATION_LOCATION = 'destination_location';
    const ERRORS = 'errors';

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getShipmentId();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return ShipmentItemInterface[]
     */
    public function getItems();

    /**
     * @return LocationInterface
     */
    public function getDestinationLocation();

    /**
     * @return ShipmentErrorInterface[]
     */
    public function getErrors();
}
