<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Temando\Shipping\Api\Shipment\ShipmentStatusInterface;
use Temando\Shipping\Model\Shipment\PackageInterface;
use Temando\Shipping\Model\Shipment\PackageItemInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Operation: Update Item Quantities.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class UpdateQtyShipped implements ShipmentOperationInterface
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentStatusInterface
     */
    private $shipmentStatus;

    /**
     * UpdateQtyShipped constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentStatusInterface $shipmentStatus
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        ShipmentStatusInterface $shipmentStatus
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentStatus = $shipmentStatus;
    }

    /**
     * Reduce the shipped items by the quantities contained in the cancelled shipment.
     *
     * @param ShipmentInterface $shipment
     * @param int $salesShipmentId
     * @throws LocalizedException
     */
    public function execute(ShipmentInterface $shipment, int $salesShipmentId): void
    {
        $status = $this->shipmentStatus->getStatusCode($shipment->getStatus());
        if ((int)$status !== ShipmentStatusInterface::STATUS_CANCELLED) {
            // do not update quantities if the remote shipment was not cancelled
            return;
        }

        $salesShipment = $this->shipmentRepository->get($salesShipmentId);
        if ((int)$salesShipment->getShipmentStatus() === ShipmentStatusInterface::STATUS_CANCELLED) {
            // do not update quantities if the local shipment cancellation was done before (quantities are up-to-date)
            return;
        }

        $salesOrder = $this->orderRepository->get($salesShipment->getOrderId());

        // collect all items from all included packages
        $packages = $shipment->getPackages() ?: [];
        $packageItems = array_reduce($packages, function (array $items, PackageInterface $package) {
            $items = array_merge($items, $package->getItems());
            return $items;
        }, []);

        // reduce each order item's qty shipped by the cancelled quantity
        array_walk($packageItems, function (PackageItemInterface $packageItem) use ($salesOrder) {
            $sku = $packageItem->getSku();
            $qty = $packageItem->getQty();

            /** @var \Magento\Sales\Model\Order $salesOrder */
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($salesOrder->getAllVisibleItems() as $orderItem) {
                if ($orderItem->getParentItem()) {
                    continue;
                }

                if ($orderItem->getSku() === $sku) {
                    $qtyShipped = $orderItem->getQtyShipped() - $qty;
                    $orderItem->setQtyShipped($qtyShipped);
                }
            }
        });

        $this->orderRepository->save($salesOrder);
    }
}
