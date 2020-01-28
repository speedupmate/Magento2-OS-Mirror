<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vertex\Tax\Model\GuestAfterPaymentWorkaroundService;

/**
 * Registers saved orders with the Workaround Service
 *
 * @see GuestAfterPaymentWorkaroundService Where everything happens
 */
class OrderSavedAfterWorkaroundObserver implements ObserverInterface
{
    /** @var GuestAfterPaymentWorkaroundService */
    private $workaroundService;

    /**
     * @param GuestAfterPaymentWorkaroundService $workaroundService
     */
    public function __construct(GuestAfterPaymentWorkaroundService $workaroundService)
    {
        $this->workaroundService = $workaroundService;
    }

    /**
     * Add an order to the Workaround Service
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        if ($order) {
            $this->workaroundService->addOrder($order);
        }
    }
}
