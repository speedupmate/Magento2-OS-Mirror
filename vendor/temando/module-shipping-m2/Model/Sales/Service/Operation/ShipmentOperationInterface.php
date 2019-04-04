<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Operation Interface.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ShipmentOperationInterface
{
    /**
     * @param ShipmentInterface $shipment
     * @param int $salesShipmentId
     * @throws LocalizedException
     */
    public function execute(ShipmentInterface $shipment, int $salesShipmentId): void;
}
