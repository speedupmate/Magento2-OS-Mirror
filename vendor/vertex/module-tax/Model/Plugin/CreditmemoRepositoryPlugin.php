<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\VertexTaxAttributeManager;

/**
 * Plugin that adds Vertex Tax extension attributes when Creditmemo Repository called
 */
class CreditmemoRepositoryPlugin
{
    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var Config */
    private $config;

    /** @var OrderItemExtensionFactory */
    private $creditmemoItemExtensionFactory;

    /**
     * @param VertexTaxAttributeManager $attributeManager
     * @param CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory
     * @param Config $config
     */
    public function __construct(
        VertexTaxAttributeManager $attributeManager,
        CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory,
        Config $config
    ) {
        $this->creditmemoItemExtensionFactory = $creditmemoItemExtensionFactory;
        $this->attributeManager = $attributeManager;
        $this->config = $config;
    }

    /**
     * Add Vertex extension attributes to creditmemo items after retrieval of a creditmemo
     *
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $resultCreditmemo
     * @return CreditmemoInterface
     */
    public function afterGet(CreditmemoRepositoryInterface $subject, CreditmemoInterface $resultCreditmemo)
    {
        if (!$this->config->isVertexActive($resultCreditmemo->getStoreId())) {
            return $resultCreditmemo;
        }

        $creditmemoItems = $resultCreditmemo->getItems();
        $orderItemIds = $this->getOrderItemIdsFromCreditmemoItemList($creditmemoItems);

        $taxCodes = $this->attributeManager->getTaxCodes($orderItemIds);
        $vertexTaxCodes = $this->attributeManager->getVertexTaxCodes($orderItemIds);
        $invoiceTextCodes = $this->attributeManager->getInvoiceTextCodes($orderItemIds);

        $this->setCreditmemoItemVertexExtensionAttributes(
            $creditmemoItems,
            $vertexTaxCodes,
            $invoiceTextCodes,
            $taxCodes
        );

        return $resultCreditmemo;
    }

    /**
     * Add Vertex extension attributes to creditmemo items after retrieval of a list of creditmemos
     *
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoSearchResultInterface $searchResult
     * @return CreditmemoSearchResultInterface
     */
    public function afterGetList(
        CreditmemoRepositoryInterface $subject,
        CreditmemoSearchResultInterface $searchResult
    ) {
        $creditmemoList = array_filter(
            $searchResult->getItems(),
            function (CreditmemoInterface $creditmemo) {
                return $this->config->isVertexActive($creditmemo->getStoreId());
            }
        );
        if (count($creditmemoList) < 1) {
            return $searchResult;
        }

        $creditmemoItems = array_reduce(
            $creditmemoList,
            function (array $carry, CreditmemoInterface $creditmemo) {
                return array_merge($carry, $creditmemo->getItems() !== null ? $creditmemo->getItems() : []);
            },
            []
        );
        $orderItemIds = $this->getOrderItemIdsFromCreditmemoItemList($creditmemoItems);

        if ($orderItemIds !== null) {
            $taxCodes = $this->attributeManager->getTaxCodes($orderItemIds);
            $vertexTaxCodes = $this->attributeManager->getVertexTaxCodes($orderItemIds);
            $invoiceTextCodes = $this->attributeManager->getInvoiceTextCodes($orderItemIds);

            $this->setCreditmemoItemVertexExtensionAttributes(
                $creditmemoItems,
                $vertexTaxCodes,
                $invoiceTextCodes,
                $taxCodes
            );
        }

        return $searchResult;
    }

    /**
     * Get Order Item Id from Creditmemo Item
     *
     * @param CreditmemoItemInterface[] $creditmemoItemArray
     * @return int[]
     */
    private function getOrderItemIdsFromCreditmemoItemList(array $creditmemoItemArray)
    {
        return array_map(
            function (CreditmemoItemInterface $item) {
                return $item->getOrderItemId();
            },
            $creditmemoItemArray
        );
    }

    /**
     * Set Creditmemo Item Vertex extension attributes to Creditmemo object
     *
     * @param $creditmemoItems
     * @param string[] $vertexTaxCodes
     * @param string[] $invoiceTextCodes
     * @param string[] $taxCodes
     * @return void
     */
    private function setCreditmemoItemVertexExtensionAttributes(
        $creditmemoItems,
        array $vertexTaxCodes,
        array $invoiceTextCodes,
        array $taxCodes
    ) {
        if (null !== $creditmemoItems) {
            foreach ($creditmemoItems as $creditmemoItem) {
                $this->setVertexTaxCodes($creditmemoItem, $vertexTaxCodes);
                $this->setInvoiceTextCodes($creditmemoItem, $invoiceTextCodes);
                $this->setTaxCodes($creditmemoItem, $taxCodes);
            }
        }
    }

    /**
     * Set Invoice Text Code extension attribute for Creditmemo Item
     *
     * @param CreditmemoItemInterface $creditmemoItem
     * @param string<int>[] $invoiceTextCodes
     * @return void
     */
    private function setInvoiceTextCodes(CreditmemoItemInterface $creditmemoItem, array $invoiceTextCodes)
    {
        $extensionAttributes = $creditmemoItem->getExtensionAttributes();

        if ($extensionAttributes && $extensionAttributes->getInvoiceTextCodes()) {
            return;
        }

        if ($invoiceTextCodes !== null && array_key_exists(
                $creditmemoItem->getOrderItemId(),
                $invoiceTextCodes
            )) {
            $creditmemoItemExtension = $extensionAttributes ?: $this->creditmemoItemExtensionFactory->create();
            $creditmemoItemExtension->setInvoiceTextCodes($invoiceTextCodes[$creditmemoItem->getOrderItemId()]);
            $creditmemoItem->setExtensionAttributes($creditmemoItemExtension);
        }
    }

    /**
     * Set Tax Code extension attribute for Creditmemo Item
     *
     * @param CreditmemoItemInterface $creditmemoItem
     * @param string<int>[] $taxCodes
     * @return void
     */
    private function setTaxCodes(CreditmemoItemInterface $creditmemoItem, array $taxCodes)
    {
        $extensionAttributes = $creditmemoItem->getExtensionAttributes();

        if ($extensionAttributes && $extensionAttributes->getTaxCodes()) {
            return;
        }

        if ($taxCodes !== null && array_key_exists($creditmemoItem->getOrderItemId(), $taxCodes)) {
            $creditmemoItemExtension = $extensionAttributes ?: $this->creditmemoItemExtensionFactory->create();
            $creditmemoItemExtension->setTaxCodes($taxCodes[$creditmemoItem->getOrderItemId()]);
            $creditmemoItem->setExtensionAttributes($creditmemoItemExtension);
        }
    }

    /**
     * Set Vertex Tax Code extension attribute for Creditmemo Item
     *
     * @param CreditmemoItemInterface $creditmemoItem
     * @param string<int>[] $vertexTaxCodes
     * @return void
     */
    private function setVertexTaxCodes(CreditmemoItemInterface $creditmemoItem, array $vertexTaxCodes)
    {
        $extensionAttributes = $creditmemoItem->getExtensionAttributes();

        if ($extensionAttributes && $extensionAttributes->getVertexTaxCodes()) {
            return;
        }

        if ($vertexTaxCodes !== null && array_key_exists($creditmemoItem->getOrderItemId(), $vertexTaxCodes)) {
            $creditmemoItemExtension = $extensionAttributes ?: $this->creditmemoItemExtensionFactory->create();
            $creditmemoItemExtension->setVertexTaxCodes($vertexTaxCodes[$creditmemoItem->getOrderItemId()]);
            $creditmemoItem->setExtensionAttributes($creditmemoItemExtension);
        }
    }
}
