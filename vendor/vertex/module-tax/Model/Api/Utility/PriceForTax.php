<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Utility;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data as TaxHelper;

class PriceForTax
{
    /** @var PriceCurrencyInterface */
    private $calculationTool;

    /** @var TaxHelper */
    private $taxHelper;

    public function __construct(
        PriceCurrencyInterface $calculationTool,
        TaxHelper $taxHelper
    ) {
        $this->calculationTool = $calculationTool;
        $this->taxHelper = $taxHelper;
    }

    public function getPriceForTaxCalculationFromQuoteItem(QuoteDetailsItemInterface $item, float $price): float
    {
        if ($this->taxHelper->applyTaxOnOriginalPrice($item->getExtensionAttributes()->getStoreId())
            && $item->getExtensionAttributes()->getPriceForTaxCalculation()
        ) {
            // Due to bugs with bundled products (magento/magento2#27700) only use price_for_tax_calc when we're only
            // supposed to apply tax on the original price.
            $priceForTaxCalculation = (float)$this->calculationTool->round(
                $item->getExtensionAttributes()->getPriceForTaxCalculation()
            );
        } else {
            $priceForTaxCalculation = $price;
        }

        return $priceForTaxCalculation;
    }

    public function getOriginalItemPriceOnQuote(
        QuoteDetailsItemInterface $item,
        float $unitPrice,
        float $parentQty = 1.0
    ): float {
        return (float)$this->calculationTool->round($item->getUnitPrice() * $item->getQuantity() * $parentQty);
    }

    public function getPriceForTaxCalculationFromOrderItem(OrderItemInterface $orderItem, float $price): float
    {
        $originalPrice = $orderItem->getOriginalPrice();
        $storeId = $orderItem->getStoreId();
        if ($originalPrice > $price && $this->taxHelper->applyTaxOnOriginalPrice($storeId)) {
            return (float)$originalPrice;
        }

        return $price;
    }
}
