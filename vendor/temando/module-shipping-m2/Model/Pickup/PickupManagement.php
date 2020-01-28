<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Pickup;

use Magento\Sales\Api\Data\OrderItemInterface;
use Temando\Shipping\Model\PickupInterface;
use Temando\Shipping\Model\Shipping\ItemExtractor;

/**
 * Temando Pickup Management
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupManagement
{
    /**
     * @var ItemExtractor
     */
    private $itemExtractor;

    /**
     * @var PickupInterface[]
     */
    private $pickups = [];

    /**
     * PickupManagement constructor.
     *
     * @param ItemExtractor $itemExtractor
     * @param PickupInterface[] $pickups
     */
    public function __construct(
        ItemExtractor $itemExtractor,
        array $pickups = []
    ) {
        $this->itemExtractor = $itemExtractor;
        $this->pickups = $pickups;
    }

    /**
     * Filter pickups by given state.
     *
     * @param string $state
     * @return PickupInterface[]
     */
    public function getPickupsByState(string $state): array
    {
        $pickups = array_filter($this->pickups, function (PickupInterface $pickup) use ($state) {
            return $pickup->getState() === $state;
        });

        return $pickups;
    }

    /**
     * Check if a pickup fulfillment with given ID can be cancelled.
     *
     * @param string $pickupId
     * @return bool
     */
    public function canCancel(string $pickupId): bool
    {
        if (!isset($this->pickups[$pickupId])) {
            return false;
        }

        $pickup = $this->pickups[$pickupId];
        $canCancel = !in_array(
            $pickup->getState(),
            [PickupInterface::STATE_CANCELLED, PickupInterface::STATE_PICKED_UP]
        );

        return $canCancel;
    }

    /**
     * Check if a pickup fulfillment with given ID can be prepared for collection.
     *
     * @param string $pickupId
     * @return bool
     */
    public function canPrepare(string $pickupId): bool
    {
        if (!isset($this->pickups[$pickupId])) {
            return false;
        }

        $pickup = $this->pickups[$pickupId];
        $canPrepare = ($pickup->getState() == PickupInterface::STATE_REQUESTED);

        return $canPrepare;
    }

    /**
     * Check if a pickup fulfillment with given ID can be collected from location.
     *
     * @param string $pickupId
     * @return bool
     */
    public function canCollect(string $pickupId): bool
    {
        if (!isset($this->pickups[$pickupId])) {
            return false;
        }

        $pickup = $this->pickups[$pickupId];
        $canCollect = ($pickup->getState() == PickupInterface::STATE_READY);

        return $canCollect;
    }

    /**
     * Obtain a list of items that are ready for collection.
     *
     * Return format: [<sku> => <qty>, <sku> => <qty>]
     *
     * @return mixed[]
     */
    public function getPreparedItems()
    {
        if (empty($this->pickups)) {
            return [];
        }

        $preparedPickups = $this->getPickupsByState(PickupInterface::STATE_READY);
        $preparedItems = array_reduce($preparedPickups, function (array $carry, PickupInterface $pickup) {
            foreach ($pickup->getItems() as $sku => $quantity) {
                if (isset($carry[$sku])) {
                    $carry[$sku]+= $quantity;
                } else {
                    $carry[$sku] = $quantity;
                }
            }

            return $carry;
        }, []);

        return $preparedItems;
    }

    /**
     * Obtain a list of items that are not yet shipped, prepared, or collected.
     *
     * Return format: [<sku> => <qty>, <sku> => <qty>]
     *
     * @param OrderItemInterface[] $orderItems
     * @return int[]
     */
    public function getOpenItems(array $orderItems)
    {
        $orderItems = $this->itemExtractor->extractShippableOrderItems($orderItems);

        $openItems = [];
        $preparedItems = $this->getPreparedItems();

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $sku = $orderItem->getSku();
            $qtyOrdered = $orderItem->getQtyOrdered();
            $qtyShipped = $orderItem->getQtyShipped();
            $qtyPrepared = isset($preparedItems[$sku]) ? $preparedItems[$sku] : 0;
            $qtyOpen = $qtyOrdered - $qtyShipped - $qtyPrepared;
            if ($qtyOpen > 0) {
                $openItems[$sku] = $qtyOpen;
            }
        }

        return $openItems;
    }

    /**
     * Check if requested items can be fulfilled.
     *
     * Returns a subset of the requested items in case some of them are already fulfilled.
     * Return format: [<sku> => <qty>, <sku> => <qty>]
     *
     * @param mixed[] $requestedItems Format: [<order_item_id> => <qty>, <order_item_id> => <qty>]
     * @param OrderItemInterface[] $orderItems
     * @return int[]
     */
    public function getRequestedItems(array $requestedItems, array $orderItems)
    {
        $openItems = $this->getOpenItems($orderItems);
        $acceptedItems = [];

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $sku = $orderItem->getSku();
            $id = $orderItem->getId();

            $qtyRequested = isset($requestedItems[$id]) ? $requestedItems[$id] : 0;
            $qtyOpen = isset($openItems[$sku]) ? $openItems[$sku] : 0;

            $qtyRequested = min($qtyRequested, $qtyOpen);

            if ($qtyRequested > 0) {
                $acceptedItems[$sku] = $qtyRequested;
            }
        }

        return $acceptedItems;
    }
}
