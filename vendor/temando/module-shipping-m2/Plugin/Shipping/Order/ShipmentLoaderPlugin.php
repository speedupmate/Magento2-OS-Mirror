<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Plugin\Shipping\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Temando\Shipping\Model\Sales\Service\ShipmentService;
use Temando\Shipping\Model\Shipment\ShipmentProviderInterface;
use Temando\Shipping\Model\Shipping\Carrier;

/**
 * ShipmentLoaderPlugin
 *
 * @package Temando\Shipping\Plugin
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentLoaderPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ShipmentProviderInterface
     */
    private $shipmentProvider;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * ShipmentLoaderPlugin constructor.
     *
     * @param RequestInterface $request
     * @param ShipmentProviderInterface $shipmentProvider
     * @param ShipmentService $shipmentService
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        ShipmentProviderInterface $shipmentProvider,
        ShipmentService $shipmentService,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->shipmentProvider = $shipmentProvider;
        $this->shipmentService = $shipmentService;
        $this->messageManager = $messageManager;
    }

    /**
     * @param ShipmentLoader $subject
     * @param bool|\Magento\Sales\Model\Order\Shipment $salesShipment
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function afterLoad(ShipmentLoader $subject, $salesShipment)
    {
        if (!$salesShipment) {
            return $salesShipment;
        }

        $controllerName = $this->request->getControllerName();
        $actionName = $this->request->getActionName();
        if (($controllerName !== 'order_shipment') || (($actionName !== 'view') && ($actionName !== 'new'))) {
            return $salesShipment;
        }

        $order = $salesShipment->getOrder();
        if (!$order instanceof OrderInterface || !$order->getData('shipping_method')) {
            // wrong type, virtual or corrupt order
            return $salesShipment;
        }

        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            return $salesShipment;
        }

        $this->shipmentProvider->setSalesShipment($salesShipment);
        if (!$salesShipment->getExtensionAttributes()->getExtShipmentId()) {
            return $salesShipment;
        }

        try {
            $shipmentReferenceId = $salesShipment->getExtensionAttributes()->getExtShipmentId();
            $shipment = $this->shipmentService->read($shipmentReferenceId, (int) $salesShipment->getEntityId());
            $this->shipmentProvider->setShipment($shipment);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $salesShipment;
    }
}
