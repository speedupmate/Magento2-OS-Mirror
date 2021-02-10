<?php

namespace Dotdigitalgroup\Sms\Plugin\Order\Shipment;

use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewShipment;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as NewShipmentAction;

class NewShipmentPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var NewShipment
     */
    private $newShipment;

    /**
     * NewShipmentPlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param NewShipment $newShipment
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NewShipment $newShipment
    ) {
        $this->orderRepository = $orderRepository;
        $this->newShipment = $newShipment;
    }

    /**
     * @param NewShipmentAction $subject
     * @param $result
     */
    public function afterExecute(
        NewShipmentAction $subject,
        $result
    ) {
        $order = $this->orderRepository->get(
            $subject
                ->getRequest()
                ->getParam('order_id')
        );

        $trackings = $subject
            ->getRequest()
            ->getParam('tracking');

        if (is_array($trackings)) {
            foreach ($trackings as $tracking) {
                $this->newShipment
                    ->buildAdditionalData(
                        $order,
                        $tracking['number'],
                        $tracking['title']
                    )->queue();
            }
        }

        return $result;
    }
}
