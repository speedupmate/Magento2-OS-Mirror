<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Vertex\Tax\Model\Config;

class FixedPriceProcessor
{
    /** @var Config  */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function invoiceItemFixedProductTax(InvoiceItemInterface $item): float
    {
        if (!$this->hasInvoiceItemGetDataMethod($item)) {
            return 0;
        }

        $fixedProductPrice = (float)$item->getData(TotalsItemInterface::KEY_WEEE_TAX_APPLIED_AMOUNT);

        if ($fixedProductPrice === 0 && !$this->isEnabledAndApplyFixedProductTax()) {
            return 0;
        }

        return (float)$fixedProductPrice;
    }

    public function invoiceItemFixedProductTaxRow(InvoiceItemInterface $item): float
    {
        if (!$this->hasInvoiceItemGetDataMethod($item)) {
            return 0;
        }

        $fixedProductPrice = (float)$item->getData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);

        if ($fixedProductPrice === 0 && !$this->isEnabledAndApplyFixedProductTax()) {
            return 0;
        }

        return (float)$fixedProductPrice;
    }

    public function creditMemoItemFixedProductTax(CreditmemoItemInterface $item): float
    {
        if (!$this->hasMemoGetDataMethod($item)) {
            return 0;
        }

        $fixedProductPrice = (float)$item->getData(TotalsItemInterface::KEY_WEEE_TAX_APPLIED_AMOUNT);

        if ($fixedProductPrice === 0 && !$this->isEnabledAndApplyFixedProductTax()) {
            return 0;
        }

        return (float)$fixedProductPrice;
    }

    public function creditMemoItemFixedProductTaxRow(CreditmemoItemInterface $item): float
    {
        if (!$this->hasMemoGetDataMethod($item)) {
            return 0;
        }

        $fixedProductPrice = (float)$item->getData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);

        if ($fixedProductPrice === 0 && !$this->isEnabledAndApplyFixedProductTax()) {
            return 0;
        }

        return (float)$fixedProductPrice;
    }

    private function isEnabledAndApplyFixedProductTax(): bool
    {
        return $this->config->isFixedProductTaxEnabled() && $this->config->isFixedProductTaxTaxable();
    }

    private function hasInvoiceItemGetDataMethod(InvoiceItemInterface $item): bool
    {
        return method_exists($item, 'getData');
    }

    private function hasMemoGetDataMethod(CreditmemoItemInterface $item): bool
    {
        return method_exists($item, 'getData');
    }
}
