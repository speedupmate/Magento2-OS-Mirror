<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\ResourceModel\GetOrderDataForOrderInFinalState;

/**
 * Match completed orders with unresolved reservations
 */
class AddCompletedOrdersToForUnresolvedReservations
{
    /**
     * @var GetOrderDataForOrderInFinalState
     */
    private $getOrderDataForOrderInFinalState;

    /**
     * @param GetOrderDataForOrderInFinalState $getOrderDataForOrderInFinalState
     */
    public function __construct(
        GetOrderDataForOrderInFinalState $getOrderDataForOrderInFinalState
    ) {
        $this->getOrderDataForOrderInFinalState = $getOrderDataForOrderInFinalState;
    }

    /**
     * Remove all entries without order
     *
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        $inconsistencies = $collector->getItems();

        $orderIds = [];
        foreach ($inconsistencies as $inconsistency) {
            $orderIds[] = $inconsistency->getObjectId();
        }

        foreach ($this->getOrderDataForOrderInFinalState->execute($orderIds) as $orderData) {
            $collector->addOrderData($orderData);
        }

        $collector->setItems($inconsistencies);
    }
}
