<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Adapter;

use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ItemRequestInterface;
use Temando\Shipping\Rest\Response\DataObject\Shipment;

/**
 * The Temando Shipment & Tracking API interface defines the supported subset of
 * operations as available at the Temando API.
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ShipmentApiInterface
{
    /**
     * Read one shipment from the platform.
     *
     * @param ItemRequestInterface $request
     * @return Shipment
     * @throws AdapterException
     */
    public function getShipment(ItemRequestInterface $request);

    /**
     * Cancel shipment at the platform.
     *
     * @param ItemRequestInterface $request
     * @return Shipment
     * @throws AdapterException
     */
    public function cancelShipment(ItemRequestInterface $request);
}
