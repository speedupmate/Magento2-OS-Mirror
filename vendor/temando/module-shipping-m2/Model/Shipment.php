<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model;

use Magento\Framework\DataObject;
use Temando\Shipping\Model\Shipment\ShipmentOriginInterface;

/**
 * Temando Shipment Entity
 *
 * This model contains a subset of data that is used in the shipping module.
 * It does not contain all data as available in its platform representation.
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class Shipment extends DataObject implements ShipmentInterface
{
    /**
     * @return string
     */
    public function getShipmentId()
    {
        return $this->getData(ShipmentInterface::SHIPMENT_ID);
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(ShipmentInterface::ORDER_ID);
    }

    /**
     * @return string
     */
    public function getOriginId()
    {
        return $this->getData(ShipmentInterface::ORIGIN_ID);
    }

    /**
     * @return ShipmentOriginInterface
     */
    public function getOriginLocation()
    {
        return $this->getData(ShipmentInterface::ORIGIN_LOCATION);
    }

    /**
     * @return \Temando\Shipping\Model\Shipment\ShipmentDestinationInterface
     */
    public function getDestinationLocation()
    {
        return $this->getData(ShipmentInterface::DESTINATION_LOCATION);
    }

    /**
     * @return \Temando\Shipping\Model\Shipment\FulfillmentInterface
     */
    public function getFulfillment()
    {
        return $this->getData(ShipmentInterface::FULFILLMENT);
    }

    /**
     * @return \Temando\Shipping\Model\Shipment\PackageInterface[]
     */
    public function getPackages()
    {
        return $this->getData(ShipmentInterface::PACKAGES);
    }

    /**
     * @return DocumentationInterface[]
     */
    public function getDocumentation()
    {
        return $this->getData(ShipmentInterface::DOCUMENTATION);
    }

    /**
     * @return bool
     */
    public function isPaperless()
    {
        return $this->getData(ShipmentInterface::IS_PAPERLESS);
    }
}
