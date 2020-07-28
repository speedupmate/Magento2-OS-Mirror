<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model\Checkout\Orderline;

use Klarna\Core\Api\BuilderInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Generate shipping order line details
 */
class Shipping extends AbstractLine
{

    /**
     * shipping is a line item collector
     *
     * @var bool
     */
    protected $isTotalCollector = false;

    /**
     * Checkout item types
     */
    const ITEM_TYPE_SHIPPING = 'shipping_fee';

    /**
     * Collect totals process.
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     * @throws \Klarna\Core\Exception
     */
    public function collect(BuilderInterface $checkout)
    {
        $object = $checkout->getObject();
        $store = null;

        if ($object instanceof CartInterface) {
            $store = $object->getStore();
            $totals = $object->getTotals();
            if (isset($totals['shipping'])) {
                /** @var \Magento\Quote\Model\Quote\Address $total */
                $total          = $totals['shipping'];
                $address        = $object->getShippingAddress();
                $discountAmount = $address->getBaseShippingDiscountAmount();
                $amount         = $address->getBaseShippingAmount() - $discountAmount;

                if ($this->klarnaConfig->isSeparateTaxLine($store)) {
                    $unitPrice = $address->getBaseShippingAmount();
                    $taxRate = 0;
                    $taxAmount = 0;
                } else {
                    $taxRate = $this->calculateShippingTax($checkout, $store);
                    $taxAmount = $address->getBaseShippingTaxAmount();
                    $unitPrice = $address->getBaseShippingInclTax();
                    $amount    = $address->getBaseShippingInclTax() - $discountAmount;
                }

                $checkout->addData(
                    [
                        'shipping_unit_price'      => $this->helper->toApiFloat($unitPrice),
                        'shipping_tax_rate'        => $this->helper->toApiFloat($taxRate),
                        'shipping_total_amount'    => $this->helper->toApiFloat($amount),
                        'shipping_tax_amount'      => $this->helper->toApiFloat($taxAmount),
                        'shipping_discount_amount' => $this->helper->toApiFloat($discountAmount),
                        'shipping_title'           => (string)$total->getTitle(),
                        'shipping_reference'       => (string)$object->getShippingAddress()->getShippingMethod()

                    ]
                );
            }
        }

        if ($object instanceof Invoice || $object instanceof Creditmemo) {
            $unitPrice = $object->getBaseShippingInclTax();
            $taxRate = $this->calculateShippingTax($checkout, $object->getStore());
            $taxAmount = $object->getShippingTaxAmount() + $object->getShippingHiddenTaxAmount();

            $checkout->addData(
                [
                    'shipping_unit_price'      => $this->helper->toApiFloat($unitPrice),
                    'shipping_tax_rate'        => $this->helper->toApiFloat($taxRate),
                    'shipping_total_amount'    => $this->helper->toApiFloat($unitPrice),
                    'shipping_tax_amount'      => $this->helper->toApiFloat($taxAmount),
                    'shipping_discount_amount' => 0,
                    'shipping_title'           => 'Shipping',
                    'shipping_reference'       => 'shipping'

                ]
            );
        }

        return $this;
    }

    /**
     * Add order details to checkout request
     *
     * @param BuilderInterface $checkout
     *
     * @return $this
     */
    public function fetch(BuilderInterface $checkout)
    {
        $object = $checkout->getObject();
        $totals = $object->getTotals();
        if (isset($totals['shipping']) && !$object->isVirtual()) {
            $checkout->addOrderLine(
                [
                    'type'                  => self::ITEM_TYPE_SHIPPING,
                    'reference'             => $checkout->getShippingReference(),
                    'name'                  => $checkout->getShippingTitle(),
                    'quantity'              => 1,
                    'unit_price'            => $checkout->getShippingUnitPrice(),
                    'tax_rate'              => $checkout->getShippingTaxRate(),
                    'total_amount'          => $checkout->getShippingTotalAmount(),
                    'total_tax_amount'      => $checkout->getShippingTaxAmount(),
                    'total_discount_amount' => $checkout->getShippingDiscountAmount()
                ]
            );
        }
        return $this;
    }
}
