<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator\Inventory;

/**
 * Unit test for \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\CatalogInventory class
 */
class CatalogInventoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\CatalogInventory
     */
    private $catalogInventory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    private $stockItemRepositoryMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->stockItemRepositoryMock = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockConfigurationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $criteriaFactoryMock = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory::class
        )->disableOriginalConstructor()->getMock();
        $criteriaMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockItemCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $criteriaFactoryMock->expects($this->once())->method('create')->willReturn($criteriaMock);
        $this->catalogInventory =
            new \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\CatalogInventory(
                $this->stockItemRepositoryMock,
                $criteriaFactoryMock,
                $this->scopeConfigMock
            );
    }

    /**
     * Test for \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\CatalogInventory::generateInventory
     * method
     */
    public function testGenerateInventory()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->scopeConfigMock->expects($this->once())->method('isShowOutOfStock')
            ->willReturn('');
        $stockMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResultMock = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\Data\StockItemCollectionInterface::class
        )->disableOriginalConstructor()->getMock();
        $searchResultMock->expects($this->once())->method('getItems')
            ->willReturn([$stockMock]);
        $this->stockItemRepositoryMock->expects($this->once())->method('getList')
            ->willReturn($searchResultMock);

        $result = $this->catalogInventory->generateInventory([$productMock], 1);
        $this->assertEquals(
            json_decode(
                '{"":{"entityId":null,"magentoId":null,"inventory":{"qty":0,"configuration":'
                . '{"status":null,"manageStock":null,"threshold":null,"productAvailable":0}}}}',
                true
            ),
            $result
        );
    }
}
