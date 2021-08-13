<?php

namespace Dotdigitalgroup\Sms\Plugin\Order\Shipment;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewShipment;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as NewShipmentAction;

class NewShipmentPlugin
{
    /**
     * @var Logger
     */
    private $logger;

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
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param NewShipment $newShipment
     */
    public function __construct(
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        NewShipment $newShipment
    ) {
        $this->logger = $logger;
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
        try {
            $order = $this->orderRepository->get(
                $subject
                    ->getRequest()
                    ->getParam('order_id')
            );
        } catch (\Exception $e) {
            $order = null;
            $this->logger->debug(
                'Could not load order for shipment',
                [(string) $e]
            );
        }

        $trackings = $subject
            ->getRequest()
            ->getParam('tracking');

        if ($order && is_array($trackings)) {
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
