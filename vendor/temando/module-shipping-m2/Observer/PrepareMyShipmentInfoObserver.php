<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\Shipment\ShipmentProviderInterface;
use Temando\Shipping\Model\Shipping\Carrier;
use Temando\Shipping\ViewModel\Shipment\Location;

/**
 * Change order info template for temando shipments in customer account.
 *
 * @package Temando\Shipping\Observer
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.temando.com/
 */
class PrepareMyShipmentInfoObserver implements ObserverInterface
{
    /**
     * @var ShipmentProviderInterface
     */
    private $shipmentProvider;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var Location
     */
    private $viewModel;

    /**
     * PrepareMyShipmentInfoObserver constructor.
     * @param ShipmentProviderInterface $shipmentProvider
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Location $viewModel
     */
    public function __construct(
        ShipmentProviderInterface $shipmentProvider,
        ShipmentRepositoryInterface $shipmentRepository,
        Location $viewModel
    ) {
        $this->shipmentProvider = $shipmentProvider;
        $this->shipmentRepository = $shipmentRepository;
        $this->viewModel = $viewModel;
    }

    /**
     * Temando provides additional order details compared to the default carriers:
     * - collection point address.
     * Apply a custom template that displays these data items.
     *
     * - event: layout_generate_blocks_after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $action = $observer->getData('full_action_name');
        if ($action !== 'sales_order_shipment') {
            return;
        }

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getData('layout');
        $infoBlock = $layout->getBlock('sales.order.info');
        if (!$infoBlock instanceof \Magento\Sales\Block\Order\Info) {
            return;
        }

        $order = $infoBlock->getOrder();
        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            return;
        }

        // add first available sales shipment with external shipment to registry
        foreach ($order->getShipmentsCollection() as $salesShipment) {
            $this->shipmentProvider->setSalesShipment($salesShipment);

            try {
                $shipmentReference = $this->shipmentRepository->getReferenceByShipmentId($salesShipment->getEntityId());
                $shipment = $this->shipmentRepository->getById($shipmentReference->getExtShipmentId());
                $this->shipmentProvider->setShipment($shipment);
            } catch (LocalizedException $exception) {
                continue;
            }

            break;
        }

        $infoBlock->setTemplate('Temando_Shipping::order/shipment/info.phtml');
        $infoBlock->setData('viewModel', $this->viewModel);
    }
}
