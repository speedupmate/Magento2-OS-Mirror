<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Plugin\InventoryApi;

use Magento\Inventory\Model\SourceItem\Command\DecrementSourceItemQty;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\ConfigurableProduct\Model\Inventory\ChangeParentStockStatus;

/**
 * Update configurable product stock status in legacy stock after decrement quantity of child stock item
 */
class UpdateParentStockStatusInLegacyStockPlugin
{
    /**
     * @var ChangeParentStockStatus
     */
    private $changeParentStockStatus;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param ChangeParentStockStatus $changeParentStockStatus
     */
    public function __construct(
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        ChangeParentStockStatus $changeParentStockStatus
    ) {
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->changeParentStockStatus = $changeParentStockStatus;
    }

    /**
     *  Make configurable product out of stock if all its children out of stock
     *
     * @param DecrementSourceItemQty $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItemDecrementData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(DecrementSourceItemQty $subject, $result, array $sourceItemDecrementData): void
    {
        $productIds = [];
        $sourceItems = array_column($sourceItemDecrementData, 'source_item');
        foreach ($sourceItems as $sourceItem) {
            $sku = $sourceItem->getSku();
            $productIds[] = (int)$this->getProductIdsBySkus->execute([$sku])[$sku];
        }
        if ($productIds) {
            $this->changeParentStockStatus->execute($productIds);
        }
    }
}
