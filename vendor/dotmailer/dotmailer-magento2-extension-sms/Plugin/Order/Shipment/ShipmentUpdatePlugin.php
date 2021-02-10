<?php

namespace Dotdigitalgroup\Sms\Plugin\Order\Shipment;

use Dotdigitalgroup\Sms\Model\Queue\OrderItem\UpdateShipment;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddTrack as UpdateShipmentAction;

class ShipmentUpdatePlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var UpdateShipment
     */
    private $updateShipment;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * ShipmentUpdatePlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param UpdateShipment $updateShipment
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        UpdateShipment $updateShipment,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->updateShipment = $updateShipment;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param UpdateShipmentAction $subject
     * @param $result
     */
    public function afterExecute(
        UpdateShipmentAction $subject,
        $result
    ) {
        $shipment = $this->shipmentRepository->get(
            $subject
                ->getRequest()
                ->getParam('shipment_id')
        );

        $orderId = $shipment->getOrderId();

        $order = $this->orderRepository->get(
            $orderId
        );

        $this->updateShipment
            ->buildAdditionalData(
                $order,
                $subject->getRequest()->getParam('number'),
                $subject->getRequest()->getParam('title')
            )->queue();

        return $result;
    }
}
