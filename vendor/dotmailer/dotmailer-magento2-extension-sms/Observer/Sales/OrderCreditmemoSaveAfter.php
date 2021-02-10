<?php

namespace Dotdigitalgroup\Sms\Observer\Sales;

use Dotdigitalgroup\Sms\Model\Queue\OrderItem\NewCreditMemo;
use Magento\Framework\Event\Observer;

class OrderCreditmemoSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var NewCreditMemo
     */
    private $newCreditMemo;

    /**
     * OrderCreditmemoSaveAfter constructor.
     * @param NewCreditMemo $newCreditMemo
     */
    public function __construct(
        NewCreditMemo $newCreditMemo
    ) {
        $this->newCreditMemo = $newCreditMemo;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        $this->newCreditMemo
            ->buildAdditionalData($order, $creditmemo)
            ->queue();
    }
}
