<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;

/**
 * Product form modifier. Add to form source item configuration data
 */
class SourceItemConfiguration extends AbstractModifier
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param LocatorInterface $locator
     * @param GetSourceItemConfigurationInterface $getSourceItemConfiguration
     * @param ScopeConfigInterface $scopeConfig
     * @param ArrayManager $arrayManager
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        LocatorInterface $locator,
        GetSourceItemConfigurationInterface $getSourceItemConfiguration,
        ScopeConfigInterface $scopeConfig,
        ArrayManager $arrayManager,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->locator = $locator;
        $this->getSourceItemConfiguration = $getSourceItemConfiguration;
        $this->scopeConfig = $scopeConfig;
        $this->arrayManager = $arrayManager;
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        if ($this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false
            || null === $product->getId()
            || !isset($data[$product->getId()]['sources']['assigned_sources'])
        ) {
            return $data;
        }

        $assignedSources = $data[$product->getId()]['sources']['assigned_sources'];
        $data[$product->getId()]['sources']['assigned_sources'] = $this->getSourceItemsConfigurationData(
            $assignedSources,
            $product
        );

        return $data;
    }

    /**
     * @param array $assignedSources
     * @param ProductInterface $product
     * @return array
     */
    private function getSourceItemsConfigurationData(array $assignedSources, ProductInterface $product): array
    {
        foreach ($assignedSources as &$source) {
            $sourceConfiguration = $this->getSourceItemConfiguration->execute(
                (string)$source[SourceInterface::SOURCE_CODE],
                $product->getSku()
            );

            $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] =
                $sourceConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY];

            $notifyQtyConfigValue = $this->getNotifyQtyConfigValue();
            if ($source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] === null) {
                $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] = $notifyQtyConfigValue;
            }
            $source['notify_stock_qty_use_default'] = '0';
            if ((float)$source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] === $notifyQtyConfigValue) {
                $source['notify_stock_qty_use_default'] = '1';
            }
        }
        unset($source);

        return $assignedSources;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $stockDataPath = $this->arrayManager->findPath(
            'stock_data',
            $meta,
            null,
            'children'
        );

        if (null === $stockDataPath || $this->isSingleSourceMode->execute()) {
            return $meta;
        }

        $backordersPath = $stockDataPath . '/children/container_notify_stock_qty/arguments/data/config';
        $meta = $this->arrayManager->set(
            $backordersPath,
            $meta,
            [
                'visible' => 0,
                'imports' => '',
            ]
        );

        return $meta;
    }

    /**
     * @return float
     */
    private function getNotifyQtyConfigValue(): float
    {
        return (float)$this->scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty');
    }
}
