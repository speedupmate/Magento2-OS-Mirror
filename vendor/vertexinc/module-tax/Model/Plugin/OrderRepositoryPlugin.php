<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\VertexTaxAttributeManager;

/**
 * Plugin that adds Vertex Tax extension attributes when Order Repository called
 */
class OrderRepositoryPlugin
{
    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var Config */
    private $config;

    /**
     * @param VertexTaxAttributeManager $attributeManager
     * @param Config $config
     */
    public function __construct(
        VertexTaxAttributeManager $attributeManager,
        Config $config
    ) {
        $this->attributeManager = $attributeManager;
        $this->config = $config;
    }

    /**
     * Add Vertex extension attributes to order items after retrieval of an order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $resultOrder)
    {
        if (!$this->config->isVertexActive($resultOrder->getStoreId())) {
            return $resultOrder;
        }

        $orderItemIds = array_keys($resultOrder->getItems());

        $taxCodes = $this->attributeManager->getTaxCodes($orderItemIds);
        $vertexTaxCodes = $this->attributeManager->getVertexTaxCodes($orderItemIds);
        $invoiceTextCodes = $this->attributeManager->getInvoiceTextCodes($orderItemIds);

        $this->setOrderItemVertexExtensionAttributes(
            $resultOrder->getItems(),
            $vertexTaxCodes,
            $invoiceTextCodes,
            $taxCodes
        );

        return $resultOrder;
    }

    /**
     * Add Vertex extension attributes to order items after retrieval of a list of orders
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orderList = array_filter(
            $searchResult->getItems(),
            function (OrderInterface $order) {
                return $this->config->isVertexActive($order->getStoreId());
            }
        );
        if (count($orderList) < 1) {
            return $searchResult;
        }

        $orderItems = array_reduce(
            $orderList,
            function (array $carry, OrderInterface $order) {
                return array_merge($carry, $order->getItems() !== null ? $order->getItems() : []);
            },
            []
        );
        $orderItemIds = array_map(
            function (OrderItemInterface $orderItem) {
                return $orderItem->getItemId();
            },
            $orderItems
        );

        if ($orderItemIds !== null) {
            $taxCodes = $this->attributeManager->getTaxCodes($orderItemIds);
            $vertexTaxCodes = $this->attributeManager->getVertexTaxCodes($orderItemIds);
            $invoiceTextCodes = $this->attributeManager->getInvoiceTextCodes($orderItemIds);

            $this->setOrderItemVertexExtensionAttributes(
                $orderItems,
                $vertexTaxCodes,
                $invoiceTextCodes,
                $taxCodes
            );
        }

        return $searchResult;
    }

    /**
     * Set Invoice Text Code extension attribute for Order Item
     *
     * @param OrderItemInterface $orderItem
     * @param string[] $invoiceTextCodeArray
     * @return void
     */
    private function setInvoiceTextCodes(OrderItemInterface $orderItem, array $invoiceTextCodeArray)
    {
        $orderItemExtension = $orderItem->getExtensionAttributes();

        if ($orderItemExtension->getInvoiceTextCodes()) {
            return;
        }

        if ($invoiceTextCodeArray !== null && array_key_exists($orderItem->getItemId(), $invoiceTextCodeArray)) {
            $orderItemExtension->setInvoiceTextCodes($invoiceTextCodeArray[$orderItem->getItemId()]);
        }
    }

    /**
     * Set Order Item Vertex extension attributes to Order object
     *
     * @param OrderItemInterface[] $orderItems
     * @param string[] $vertexTaxCodes
     * @param string[] $invoiceTextCodes
     * @param string[] $taxCodes
     * @return void
     */
    private function setOrderItemVertexExtensionAttributes(
        array $orderItems,
        array $vertexTaxCodes,
        array $invoiceTextCodes,
        array $taxCodes
    ) {
        if (null !== $orderItems) {
            foreach ($orderItems as $orderItem) {
                $this->setVertexTaxCodes($orderItem, $vertexTaxCodes);
                $this->setInvoiceTextCodes($orderItem, $invoiceTextCodes);
                $this->setTaxCodes($orderItem, $taxCodes);
            }
        }
    }

    /**
     * Set Invoice Tax Code extension attribute for Order Item
     *
     * @param OrderItemInterface $orderItem
     * @param string[] $taxCodeArray
     * @return void
     */
    private function setTaxCodes(OrderItemInterface $orderItem, array $taxCodeArray)
    {
        $orderItemExtension = $orderItem->getExtensionAttributes();

        if ($orderItemExtension->getTaxCodes()) {
            return;
        }

        if ($taxCodeArray !== null && array_key_exists($orderItem->getItemId(), $taxCodeArray)) {
            $orderItemExtension->setTaxCodes($taxCodeArray[$orderItem->getItemId()]);
        }
    }

    /**
     * Set Vertex Tax Code extension attribute for Order Item
     *
     * @param OrderItemInterface $orderItem
     * @param string[] $vertexTaxCodeArray
     * @return void
     */
    private function setVertexTaxCodes(OrderItemInterface $orderItem, array $vertexTaxCodeArray)
    {
        $orderItemExtension = $orderItem->getExtensionAttributes();

        if ($orderItemExtension->getVertexTaxCodes()) {
            return;
        }

        if ($vertexTaxCodeArray !== null && array_key_exists($orderItem->getItemId(), $vertexTaxCodeArray)) {
            $orderItemExtension->setVertexTaxCodes($vertexTaxCodeArray[$orderItem->getItemId()]);
        }
    }
}
