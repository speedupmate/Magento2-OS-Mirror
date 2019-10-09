<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Sales\Api\Data\TotalInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\ShippingInterfaceFactory;
use Magento\Sales\Api\Data\TotalInterfaceFactory;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;

/**
 * Create Shipping Assignment Extension Attributes for use with Vertex Invoicing
 *
 * This class is intended to be a compatibility layer, as we shouldn't need to
 * create these on our own.  However, when Magento is placing a new order that
 * order does not contain the Shipping Assignment extension attributes, and
 * loading said order again to generate them can cause errors on some versions
 * of Magento.
 */
class ShippingAssignmentExtensionLoader
{
    /** @var ShippingAssignmentInterfaceFactory */
    private $assignmentFactory;

    /** @var OrderExtensionFactory */
    private $extensionFactory;

    /** @var ShippingInterfaceFactory */
    private $shippingFactory;

    /** @var TotalInterfaceFactory */
    private $totalFactory;

    /**
     * @param OrderExtensionFactory $extensionFactory
     * @param ShippingInterfaceFactory $shippingFactory
     * @param TotalInterfaceFactory $totalFactory
     * @param ShippingAssignmentInterfaceFactory $assignmentFactory
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        ShippingInterfaceFactory $shippingFactory,
        TotalInterfaceFactory $totalFactory,
        ShippingAssignmentInterfaceFactory $assignmentFactory
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->shippingFactory = $shippingFactory;
        $this->totalFactory = $totalFactory;
        $this->assignmentFactory = $assignmentFactory;
    }

    /**
     * Create Shipping and ShippingAssignment objects for an Order
     *
     * @param Order $order
     * @return Order
     */
    public function loadOnOrder(Order $order)
    {
        if ($order->getExtensionAttributes() && $order->getExtensionAttributes()->getShippingAssignments()) {
            return $order;
        }

        /** @var OrderExtensionInterface $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes() ?: $this->extensionFactory->create();

        /** @var ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shipping->setAddress($shippingAddress);
        }
        $shipping->setMethod($order->getShippingMethod());
        $shipping->setTotal($this->getShippingTotal($order));

        /** @var ShippingAssignmentInterface $assignment */
        $assignment = $this->assignmentFactory->create();
        $assignment->setShipping($shipping);
        $assignment->setItems($order->getItems());
        $assignment->setStockId($order->getStockId());

        $extensionAttributes->setShippingAssignments([$assignment]);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Retrieve the shipping total information
     *
     * Code duplicated from {@see Magento\Sales\Model\Order\ShippingBuilder::getTotal()}
     * Method code © Magento, Inc. All rights reserved.
     * Method code has been modified to take a parameter instead of relying on object state
     *
     * @param Order $order
     * @return TotalInterface
     */
    private function getShippingTotal(Order $order)
    {
        /** @var TotalInterface $total */
        $total = $this->totalFactory->create();
        $total->setBaseShippingAmount($order->getBaseShippingAmount());
        $total->setBaseShippingCanceled($order->getBaseShippingCanceled());
        $total->setBaseShippingDiscountAmount($order->getBaseShippingDiscountAmount());
        $total->setBaseShippingDiscountTaxCompensationAmnt($order->getBaseShippingDiscountTaxCompensationAmnt());
        $total->setBaseShippingInclTax($order->getBaseShippingInclTax());
        $total->setBaseShippingInvoiced($order->getBaseShippingInvoiced());
        $total->setBaseShippingRefunded($order->getBaseShippingRefunded());
        $total->setBaseShippingTaxAmount($order->getBaseShippingTaxAmount());
        $total->setBaseShippingTaxRefunded($order->getBaseShippingTaxRefunded());
        $total->setShippingAmount($order->getShippingAmount());
        $total->setShippingCanceled($order->getShippingCanceled());
        $total->setShippingDiscountAmount($order->getShippingDiscountAmount());
        $total->setShippingDiscountTaxCompensationAmount($order->getShippingDiscountTaxCompensationAmount());
        $total->setShippingInclTax($order->getShippingInclTax());
        $total->setShippingInvoiced($order->getShippingInvoiced());
        $total->setShippingRefunded($order->getShippingRefunded());
        $total->setShippingTaxAmount($order->getShippingTaxAmount());
        $total->setShippingTaxRefunded($order->getShippingTaxRefunded());
        return $total;
    }
}
