<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service\Operation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
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
     * UpdateQtyShipped constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
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
        $salesShipment = $this->shipmentRepository->get($salesShipmentId);
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
