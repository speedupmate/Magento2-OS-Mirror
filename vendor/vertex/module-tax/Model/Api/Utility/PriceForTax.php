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
        if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getPriceForTaxCalculation()) {
            $priceForTaxCalculation = (float) $this->calculationTool->round(
                $item->getExtensionAttributes()->getPriceForTaxCalculation()
            );
        } else {
            $priceForTaxCalculation = $price;
        }

        return $priceForTaxCalculation;
    }

    public function getOriginalItemPriceOnQuote(QuoteDetailsItemInterface $item, float $unitPrice): float
    {
        return (float) $this->calculationTool->round($item->getUnitPrice() * $item->getQuantity());
    }

    public function getPriceForTaxCalculationFromOrderItem(OrderItemInterface $orderItem, float $price): float
    {
        $originalPrice = $orderItem->getOriginalPrice();
        $storeId = $orderItem->getStoreId();
        if ($originalPrice > $price && $this->taxHelper->applyTaxOnOriginalPrice($storeId)) {
            return (float) $originalPrice;
        }

        return $price;
    }
}
