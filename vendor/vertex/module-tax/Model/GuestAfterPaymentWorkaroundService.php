<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverFactory;
use Magento\Framework\EventFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Vertex\Tax\Observer\InvoiceSavedAfterObserver;
use Vertex\Tax\Observer\InvoiceSavedAfterObserverFactory;
use Vertex\Tax\Observer\OrderSavedAfterObserver;
use Vertex\Tax\Observer\OrderSavedAfterObserverFactory;

/**
 * Provides methods for working around _save_commit_after events not firing during Guest Checkout
 *
 * With love to our colleagues at Temando who know our pain.
 *
 * When submitting Vertex Invoices we receive back Tax Codes, Vertex Tax Codes, and Invoice Text Codes per line-item on
 * the invoice.  We want to save these records to the database for implementors' use with their ERP, if desired.
 *
 * In order to do that, we must have the order item ids.  If an order is captured during placement, these will not be
 * available during the order_save_after event.  As such, we're relegated to waiting for the order_save_commit_after
 * event.  However, this event is not thrown during guest checkout procedures (due to the opening of db transactions
 * that do not trigger callbacks once they're committed).  For these purposes, we use a plugin on the guest checkout
 * routine to run this service - which triggers the same observers that would normally trigger in such an event.
 *
 * We support forward compatibility by having those observers clear out the objects if they're ever run (so in the
 * event this bug is fixed this workaround service should do nothing)
 */
class GuestAfterPaymentWorkaroundService
{
    /** @var ResourceConnection */
    private $connectionPull;

    /** @var EventFactory */
    private $eventFactory;

    /** @var InvoiceSavedAfterObserverFactory */
    private $invoiceObserverFactory;

    /** @var Invoice[] */
    private $invoices = [];

    /** @var ObserverFactory */
    private $observerFactory;

    /** @var OrderSavedAfterObserverFactory */
    private $orderObserverFactory;

    /** @var Order[] */
    private $orders = [];

    /**
     * @param ObserverFactory $observerFactory
     * @param EventFactory $eventFactory
     * @param InvoiceSavedAfterObserverFactory $invoiceObserverFactory
     * @param OrderSavedAfterObserverFactory $orderObserverFactory
     * @param ResourceConnection $connectionPull
     */
    public function __construct(
        ObserverFactory $observerFactory,
        EventFactory $eventFactory,
        InvoiceSavedAfterObserverFactory $invoiceObserverFactory,
        OrderSavedAfterObserverFactory $orderObserverFactory,
        ResourceConnection $connectionPull
    ) {
        $this->observerFactory = $observerFactory;
        $this->eventFactory = $eventFactory;
        $this->invoiceObserverFactory = $invoiceObserverFactory;
        $this->orderObserverFactory = $orderObserverFactory;
        $this->connectionPull = $connectionPull;
    }

    /**
     * Add an invoice to the list that will be processed
     *
     * @param Invoice $invoice
     * @return void
     */
    public function addInvoice(Invoice $invoice)
    {
        $this->invoices[] = $invoice;
    }

    /**
     * Add an order to the list that will be processed
     *
     * @param Order $order
     * @return void
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;
    }

    /**
     * Clear all invoices from the list to prevent processing
     *
     * @return void
     */
    public function clearInvoices()
    {
        $this->invoices = [];
    }

    /**
     * Clear all orders from the list to prevent processing
     *
     * @return void
     */
    public function clearOrders()
    {
        $this->orders = [];
    }

    /**
     * Manually trigger observers as if _save_commit_after events had happened
     *
     * @return void
     */
    public function process()
    {
        $invoices = $this->invoices;
        $orders = $this->orders;

        if (!count($invoices) && !count($orders)) {
            // Early exit if there's nothing to do
            return;
        }

        // Intentionally create the same bug that we're working around so that we don't accidentally call the commit
        // hooks during the procedure (by saving logs - for example) and then essentially double-calling our observers

        $salesConnection = $this->connectionPull->getConnection('sales');
        $checkoutConnection = $this->connectionPull->getConnection('checkout');

        $salesConnection->beginTransaction();
        $checkoutConnection->beginTransaction();

        try {
            foreach ($invoices as $invoice) {
                /** @var Observer $observer */
                $observer = $this->observerFactory->create(Observer::class);
                $event = $this->eventFactory->create();

                $event->setData('invoice', $invoice);
                $observer->setEvent($event);

                /** @var InvoiceSavedAfterObserver $invoiceObserver */
                $invoiceObserver = $this->invoiceObserverFactory->create();
                $invoiceObserver->execute($observer);
            }
            $this->clearInvoices();

            foreach ($orders as $order) {
                /** @var Observer $observer */
                $observer = $this->observerFactory->create(Observer::class);
                $event = $this->eventFactory->create();

                $event->setData('order', $order);
                $observer->setEvent($event);

                /** @var OrderSavedAfterObserver $invoiceObserver */
                $invoiceObserver = $this->orderObserverFactory->create();
                $invoiceObserver->execute($observer);
            }
            $this->clearOrders();
        } catch (Exception $e) {
            throw $e;
        } finally {
            // Our transactions are solely for bug recreation purposes - we want whatever tried to save to save
            $salesConnection->commit();
            $checkoutConnection->commit();
        }
    }
}
