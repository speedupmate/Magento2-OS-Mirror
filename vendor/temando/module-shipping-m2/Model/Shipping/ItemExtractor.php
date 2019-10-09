<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Shipping;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class Item Extractor
 *
 * @package Temando\Shipping\Model
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ItemExtractor
{
    /**
     * Extract shippable items from given quote items.
     *
     * @param AbstractItem[] $quoteItems
     * @return AbstractItem[]
     */
    public function extractShippableQuoteItems(array $quoteItems): array
    {
        $quoteItems = array_reduce($quoteItems, function (array $items, AbstractItem $quoteItem) {
            // skip virtual and child items
            if ($quoteItem->getParentItem()) {
                return $items;
            }

            // handle bundles
            if ($quoteItem->isShipSeparately()) {
                $items = array_merge($items, $quoteItem->getChildren());
            } else {
                $items[]= $quoteItem;
            }

            return $items;
        }, []);

        return $quoteItems;
    }

    /**
     * Extract shippable items from given order items.
     *
     * @param OrderItemInterface[] $orderItems
     * @return OrderItemInterface[]
     */
    public function extractShippableOrderItems(array $orderItems): array
    {
        $orderItems = array_reduce($orderItems, function (array $items, Item $orderItem) {
            // skip virtual and child items
            if ($orderItem->getIsVirtual() || $orderItem->getParentItem()) {
                return $items;
            }

            // handle bundles
            if ($orderItem->isShipSeparately()) {
                $items = array_merge($items, $orderItem->getChildrenItems());
            } else {
                $items[]= $orderItem;
            }

            return $items;
        }, []);

        return $orderItems;
    }
}
