<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Shipment;

use Magento\Framework\DataObject;

/**
 * Temando Shipment Summary
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentSummary extends DataObject implements ShipmentSummaryInterface
{
    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(ShipmentSummaryInterface::ORDER_ID);
    }

    /**
     * @return string
     */
    public function getShipmentId()
    {
        return $this->getData(ShipmentSummaryInterface::SHIPMENT_ID);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(ShipmentSummaryInterface::STATUS);
    }

    /**
     * @return ShipmentItemInterface[]
     */
    public function getItems()
    {
        return $this->getData(ShipmentSummaryInterface::ITEMS);
    }

    /**
     * @return LocationInterface
     */
    public function getDestinationLocation()
    {
        return $this->getData(ShipmentSummaryInterface::DESTINATION_LOCATION);
    }

    /**
     * @return ShipmentErrorInterface[]
     */
    public function getErrors()
    {
        return $this->getData(ShipmentSummaryInterface::ERRORS);
    }
}
