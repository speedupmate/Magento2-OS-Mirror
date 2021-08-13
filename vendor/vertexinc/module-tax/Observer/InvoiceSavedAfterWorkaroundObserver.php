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
 * Registers saved invoices with the Workaround Service
 *
 * @see GuestAfterPaymentWorkaroundService Where everything happens
 */
class InvoiceSavedAfterWorkaroundObserver implements ObserverInterface
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
     * Add an invoice to the Workaround Service
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getData('invoice');
        if ($invoice) {
            $this->workaroundService->addInvoice($invoice);
        }
    }
}
