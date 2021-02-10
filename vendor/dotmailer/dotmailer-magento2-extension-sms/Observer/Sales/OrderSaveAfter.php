<?php

namespace Dotdigitalgroup\Sms\Observer\Sales;

use Dotdigitalgroup\Sms\Model\Queue\OrderItem\UpdateOrder;
use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewOrder;
use Magento\Framework\Event\Observer;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var UpdateOrder
     */
    private $updateOrder;

    /**
     * @var NewOrder
     */
    private $newOrder;

    /**
     * OrderSaveAfter constructor.
     * @param UpdateOrder $updateOrder
     * @param NewOrder $newOrder
     */
    public function __construct(
        UpdateOrder $updateOrder,
        NewOrder $newOrder
    ) {
        $this->updateOrder = $updateOrder;
        $this->newOrder = $newOrder;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($this->isCanceledOrHolded($order)) {
            $this->updateOrder
                ->buildAdditionalData($order)
                ->queue($order);
        }

        if ($this->isNewOrder($order)) {
            $this->newOrder
                ->buildAdditionalData($order)
                ->queue();
        }
    }

    /**
     * @param $order
     * @return bool
     */
    private function isCanceledOrHolded($order)
    {
        return $order->getStatus() === 'canceled' || $order->getStatus() === 'holded';
    }

    /**
     * @param $order
     * @return bool
     */
    private function isNewOrder($order)
    {
        return $order->getCreatedAt() === $order->getUpdatedAt();
    }
}
