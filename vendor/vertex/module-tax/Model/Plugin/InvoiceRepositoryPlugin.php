<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemExtensionFactory;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\VertexTaxAttributeManager;

/**
 * Plugin that adds Vertex Tax extension attributes when the Invoice Repository is called
 */
class InvoiceRepositoryPlugin
{
    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var Config */
    private $config;

    /** @var InvoiceItemExtensionFactory */
    private $invoiceItemExtensionFactory;

    /**
     * @param VertexTaxAttributeManager $attributeManager
     * @param InvoiceItemExtensionFactory $invoiceItemExtensionFactory
     * @param Config $config
     */
    public function __construct(
        VertexTaxAttributeManager $attributeManager,
        InvoiceItemExtensionFactory $invoiceItemExtensionFactory,
        Config $config
    ) {
        $this->attributeManager = $attributeManager;
        $this->invoiceItemExtensionFactory = $invoiceItemExtensionFactory;
        $this->config = $config;
    }

    /**
     * Add Vertex extension attributes to an invoice's items after it is retrieved
     *
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $invoice
     * @return InvoiceInterface
     */
    public function afterGet(InvoiceRepositoryInterface $subject, InvoiceInterface $invoice)
    {
        if (!$this->config->isVertexActive($invoice->getStoreId())) {
            return $invoice;
        }

        $invoiceItems = $invoice->getItems();
        $orderItemIds = $this->getOrderItemIdsFromInvoiceItemList($invoiceItems);

        $taxCodes = $this->attributeManager->getTaxCodes($orderItemIds);
        $vertexTaxCodes = $this->attributeManager->getVertexTaxCodes($orderItemIds);
        $invoiceTextCodes = $this->attributeManager->getInvoiceTextCodes($orderItemIds);

        $this->setInvoiceItemVertexExtensionAttributes(
            $invoiceItems,
            $vertexTaxCodes,
            $invoiceTextCodes,
            $taxCodes
        );

        return $invoice;
    }

    /**
     * Retrieve the Order Item IDs from a list of Invoice Items
     *
     * @param InvoiceItemInterface[] $invoiceItems
     * @return int[]
     */
    private function getOrderItemIdsFromInvoiceItemList(array $invoiceItems)
    {
        return array_map(
            function (InvoiceItemInterface $invoiceItem) {
                return $invoiceItem->getOrderItemId();
            },
            $invoiceItems
        );
    }

    /**
     * Assign Invoice Item Vertex extension attributes to the Invoice Item object
     *
     * @param InvoiceItemInterface[] $invoiceItems
     * @param string<int>[] $vertexTaxCodes
     * @param string<int>[] $invoiceTextCodes
     * @param string<int>[] $taxCodes
     * @return void
     */
    private function setInvoiceItemVertexExtensionAttributes(
        array $invoiceItems,
        array $vertexTaxCodes,
        array $invoiceTextCodes,
        array $taxCodes
    ) {
        if ($invoiceItems === null) {
            return;
        }

        foreach ($invoiceItems as $invoiceItem) {
            $this->setVertexTaxCodes($invoiceItem, $vertexTaxCodes);
            $this->setInvoiceTextCodes($invoiceItem, $invoiceTextCodes);
            $this->setTaxCodes($invoiceItem, $taxCodes);
        }
    }

    /**
     * Set InvoiceTextCode extension attribute for Invoice Item
     *
     * @param InvoiceItemInterface $invoiceItem
     * @param string<int>[] $invoiceTextCodes
     * @return void
     */
    private function setInvoiceTextCodes(InvoiceItemInterface $invoiceItem, array $invoiceTextCodes)
    {
        $extensionAttributes = $invoiceItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getInvoiceTextCodes()) {
            return;
        }

        if ($invoiceTextCodes !== null && array_key_exists($invoiceItem->getOrderItemId(), $invoiceTextCodes)) {
            $extensionAttributes = $extensionAttributes ?: $this->invoiceItemExtensionFactory->create();
            $extensionAttributes->setInvoiceTextCodes($invoiceTextCodes[$invoiceItem->getOrderItemId()]);
            $invoiceItem->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * Set the TaxCode extension attribute for an Invoice Item
     *
     * @param InvoiceItemInterface $invoiceItem
     * @param string<int>[] $taxCodes
     * @return void
     */
    private function setTaxCodes(InvoiceItemInterface $invoiceItem, array $taxCodes)
    {
        $extensionAttributes = $invoiceItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getTaxCodes()) {
            return;
        }

        if ($taxCodes !== null && array_key_exists($invoiceItem->getOrderItemId(), $taxCodes)) {
            $extensionAttributes = $extensionAttributes ?: $this->invoiceItemExtensionFactory->create();
            $extensionAttributes->setTaxCodes($taxCodes[$invoiceItem->getOrderItemId()]);
            $invoiceItem->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * Set the VertexTaxCode extension attribute for an Invoice Item
     *
     * @param InvoiceItemInterface $invoiceItem
     * @param string<int>[] $vertexTaxCodes
     * @return void
     */
    private function setVertexTaxCodes(InvoiceItemInterface $invoiceItem, array $vertexTaxCodes)
    {
        $extensionAttributes = $invoiceItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getVertexTaxCodes()) {
            return;
        }

        if ($vertexTaxCodes !== null && array_key_exists($invoiceItem->getOrderItemId(), $vertexTaxCodes)) {
            $extensionAttributes = $extensionAttributes ?: $this->invoiceItemExtensionFactory->create();
            $extensionAttributes->setVertexTaxCodes($vertexTaxCodes[$invoiceItem->getOrderItemId()]);
            $invoiceItem->setExtensionAttributes($extensionAttributes);
        }
    }
}
