<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Sales\Total\Quote\Tax;
use Vertex\Tax\Model\Config;

/**
 * Plugins to the Tax Total
 *
 * @see Tax
 */
class TaxPlugin
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add Vertex product codes and custom tax classes to extra taxables
     *
     * @param Tax $subject
     * @param QuoteDetailsItemInterface[] $items
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Address $address
     * @return QuoteDetailsItemInterface[]
     * @see Tax::mapQuoteExtraTaxables()
     */
    public function afterMapQuoteExtraTaxables(
        Tax $subject,
        array $items,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Address $address
    ): array {
        $store = $address->getQuote()->getStore();
        $storeId = $store->getStoreId();

        if (!$this->config->isVertexActive($storeId) || !$this->config->isTaxCalculationEnabled($storeId)) {
            return $items;
        }

        foreach ($items as $item) {
            switch ($item->getType()) {
                case 'quote_gw':
                    $sku = $this->config->getGiftWrappingOrderCode($store);
                    $taxClassId = $this->config->getGiftWrappingOrderClass($store);
                    break;
                case 'printed_card_gw':
                    $sku = $this->config->getPrintedGiftcardCode($store);
                    $taxClassId = $this->config->getPrintedGiftcardClass($store);
                    break;
                default:
                    continue 2;
            }
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes->setVertexProductCode($sku);
            $item->setTaxClassId($taxClassId);
            if ($item->getTaxClassKey() && $item->getTaxClassKey()->getType() === TaxClassKeyInterface::TYPE_ID) {
                $item->getTaxClassKey()->setValue($taxClassId);
            }
        }

        return $items;
    }
}
