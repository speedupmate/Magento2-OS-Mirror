<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

class ShippingAddressRetriever
{
    /**
     * Retrieve the shipping address from an Order
     *
     * @param OrderInterface $order
     * @return OrderAddressInterface|null
     */
    public function getShippingFromOrder(OrderInterface $order)
    {
        if ($order instanceof Order && $order->getShippingAddress()) {
            return $order->getShippingAddress();
        }
        return $this->getShippingAssignmentAddress($order);
    }

    /**
     * Retrieve the shipping address from a Quote
     *
     * @param CartInterface $cart
     * @return AddressInterface|null
     */
    public function getShippingFromQuote(CartInterface $cart)
    {
        if ($cart instanceof Quote && $cart->getShippingAddress()) {
            return $cart->getShippingAddress();
        }
        return $this->getShippingAssignmentAddress($cart);
    }

    /**
     * Retrieve the shipping address from the shipping assignments
     *
     * @param CartInterface|OrderInterface $object
     * @return AddressInterface|OrderAddressInterface|null
     */
    private function getShippingAssignmentAddress($object)
    {
        if (!$object instanceof ExtensibleDataInterface) {
            return null;
        }

        return $object->getExtensionAttributes() !== null
        && $object->getExtensionAttributes()->getShippingAssignments()
        && $object->getExtensionAttributes()->getShippingAssignments()[0]
        && $object->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()
            ? $object->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()->getAddress()
            : null;
    }
}
