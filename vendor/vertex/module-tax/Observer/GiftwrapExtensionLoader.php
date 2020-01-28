<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoExtensionInterface;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceExtensionInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Vertex\Tax\Model\ModuleManager;

/**
 * Loads Giftwrap extension attributes on recently-saved objects
 */
class GiftwrapExtensionLoader
{
    /** @var CreditmemoExtensionFactory */
    private $creditmemoExtensionFactory;

    /** @var InvoiceExtensionFactory */
    private $invoiceExtensionFactory;

    /** @var OrderItemExtensionFactory */
    private $itemExtensionFactory;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var OrderExtensionFactory */
    private $orderExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param InvoiceExtensionFactory $invoiceExtensionFactory
     * @param CreditmemoExtensionFactory $creditmemoExtensionFactory
     * @param OrderItemExtensionFactory $itemExtensionFactory
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        InvoiceExtensionFactory $invoiceExtensionFactory,
        CreditmemoExtensionFactory $creditmemoExtensionFactory,
        OrderItemExtensionFactory $itemExtensionFactory,
        ModuleManager $moduleManager
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->itemExtensionFactory = $itemExtensionFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Load the Giftwrapping module Extension Attributes onto a Creditmemo
     *
     * @param Creditmemo $originalCreditmemo
     * @return Creditmemo
     */
    public function loadOnCreditmemo(Creditmemo $originalCreditmemo)
    {
        if (!$this->moduleManager->isEnabled('Magento_GiftWrapping')) {
            return $originalCreditmemo;
        }

        /** @var Creditmemo $creditmemo */
        $creditmemo = clone $originalCreditmemo;

        if (!$originalCreditmemo->getExtensionAttributes()) {
            /** @var CreditmemoExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->creditmemoExtensionFactory->create();
        } else {
            $extensionAttributes = clone $originalCreditmemo->getExtensionAttributes();
        }

        if (!$extensionAttributes->getGwBasePrice()) {
            $extensionAttributes->setGwBasePrice($originalCreditmemo->getData('gw_base_price'));
        }

        if (!$extensionAttributes->getGwCardBasePrice()) {
            $extensionAttributes->setGwCardBasePrice($originalCreditmemo->getData('gw_card_base_price'));
        }

        if (!$extensionAttributes->getGwItemsBasePrice()) {
            $extensionAttributes->setGwItemsBasePrice($originalCreditmemo->getData('gw_items_base_price'));
        }

        return $creditmemo->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Load the Giftwrapping module Extension Attributes onto an Invoice
     *
     * @param Invoice $originalInvoice
     * @return Invoice
     */
    public function loadOnInvoice(Invoice $originalInvoice)
    {
        if (!$this->moduleManager->isEnabled('Magento_GiftWrapping')) {
            return $originalInvoice;
        }

        /** @var Invoice $invoice */
        $invoice = clone $originalInvoice;

        if (!$originalInvoice->getExtensionAttributes()) {
            /** @var InvoiceExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->invoiceExtensionFactory->create();
        } else {
            $extensionAttributes = clone $originalInvoice->getExtensionAttributes();
        }

        if (!$extensionAttributes->getGwBasePrice()) {
            $extensionAttributes->setGwBasePrice($originalInvoice->getData('gw_base_price'));
        }

        if (!$extensionAttributes->getGwCardBasePrice()) {
            $extensionAttributes->setGwCardBasePrice($originalInvoice->getData('gw_card_base_price'));
        }

        if (!$extensionAttributes->getGwItemsBasePrice()) {
            $extensionAttributes->setGwItemsBasePrice($originalInvoice->getData('gw_items_base_price'));
        }

        return $invoice->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Load the Giftwrapping module Extension Attributes onto an Order
     *
     * @param Order $order
     * @return Order
     */
    public function loadOnOrder(Order $order)
    {
        if (!$this->moduleManager->isEnabled('Magento_GiftWrapping')) {
            return $order;
        }

        foreach ($order->getItems() as $item) {
            $this->loadOnOrderItem($item);
        }

        /** @var OrderExtensionInterface $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create();
        $extensionAttributes->setGwBasePrice($order->getData('gw_base_price'));
        $extensionAttributes->setGwCardBasePrice($order->getData('gw_card_base_price'));
        $extensionAttributes->setGwItemsBasePrice($order->getData('gw_items_base_price'));

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Load the Giftwrapping module Extension Attributes onto an Order Item
     *
     * @param Item $item
     * @return void
     */
    public function loadOnOrderItem(Item $item)
    {
        if (!$this->moduleManager->isEnabled('Magento_GiftWrapping')) {
            return;
        }

        /** @var OrderItemExtensionInterface $extensionAttributes */
        $extensionAttributes = $item->getExtensionAttributes() ?: $this->itemExtensionFactory->create();
        $extensionAttributes->setGwBasePrice($item->getData('gw_base_price'));

        $item->setExtensionAttributes($extensionAttributes);
    }
}
