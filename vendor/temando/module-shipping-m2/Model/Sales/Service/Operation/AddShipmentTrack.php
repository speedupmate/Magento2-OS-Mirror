<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Temando\Shipping\Model\ShipmentInterface;
use Temando\Shipping\Model\Shipping\Carrier;

/**
 * Temando Shipment Operation: Add Track.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AddShipmentTrack implements ShipmentOperationInterface
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackUpdateFactory;

    /**
     * AddShipmentTrack constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentTrackInterfaceFactory $trackUpdateFactory
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentTrackInterfaceFactory $trackUpdateFactory
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->trackUpdateFactory = $trackUpdateFactory;
    }

    /**
     * Add tracking reference to local shipment.
     *
     * @param ShipmentInterface $shipment
     * @param int $salesShipmentId
     * @throws LocalizedException
     */
    public function execute(ShipmentInterface $shipment, int $salesShipmentId): void
    {
        $fulfillment = $shipment->getFulfillment();
        if (!$fulfillment || !$fulfillment->getTrackingReference()) {
            // no fulfillment with tracking number available, nothing to add.
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment $salesShipment */
        $salesShipment = $this->shipmentRepository->get($salesShipmentId);
        $tracks = $salesShipment->getTracksCollection();
        foreach ($tracks as $track) {
            if ($track->getTrackNumber() === $fulfillment->getTrackingReference()) {
                // tracking number already exists, nothing to do.
                return;
            }
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $tracking */
        $tracking = $this->trackUpdateFactory->create();
        $tracking->setCarrierCode(Carrier::CODE);
        $tracking->setTitle($fulfillment->getServiceName());
        $tracking->setTrackNumber($fulfillment->getTrackingReference());

        $salesShipment->addTrack($tracking);
        $this->shipmentRepository->save($salesShipment);
    }
}
