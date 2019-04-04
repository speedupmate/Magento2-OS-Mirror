<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Temando\Shipping\Api\Shipment\ShipmentStatusInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Operation: Update Status.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class UpdateShipmentStatus implements ShipmentOperationInterface
{
    /**
     * @var ShipmentStatusInterface
     */
    private $shipmentStatus;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * SyncShipmentStatus constructor.
     * @param ShipmentStatusInterface $shipmentStatus
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        ShipmentStatusInterface $shipmentStatus,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->shipmentStatus = $shipmentStatus;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Update local shipment status if platform status differs.
     *
     * @param ShipmentInterface $shipment
     * @param int $salesShipmentId
     * @throws LocalizedException
     */
    public function execute(ShipmentInterface $shipment, int $salesShipmentId): void
    {
        $status = $this->shipmentStatus->getStatusCode($shipment->getStatus());
        if (!$status) {
            // unknown status, cannot update
            return;
        }

        $salesShipment = $this->shipmentRepository->get($salesShipmentId);
        if ($salesShipment->getShipmentStatus() == $status) {
            // status in sync, nothing to do
            return;
        }

        $salesShipment->setShipmentStatus($status);
        $this->shipmentRepository->save($salesShipment);
    }
}
