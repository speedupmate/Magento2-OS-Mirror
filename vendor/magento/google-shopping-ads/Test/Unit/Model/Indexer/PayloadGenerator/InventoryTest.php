<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator;

use Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\InventoryGeneratorFactory;
use Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\InventoryGeneratorInterface;

/**
 * Unit tests for \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory class
 */
class InventoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory
     */
    private $inventory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InventoryGeneratorInterface
     */
    private $inventoryGenerator;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $inventoryGeneratorFactory = $this->getMockBuilder(InventoryGeneratorFactory::class)
        ->setMethods(['create'])
        ->disableOriginalConstructor()
        ->getMock();
        $this->inventoryGenerator = $this->getMockBuilder(InventoryGeneratorInterface::class)
            ->setMethods(['generateInventory'])
            ->disableOriginalConstructor()
            ->getMock();
        $inventoryGeneratorFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($this->inventoryGenerator);
        $this->inventory = new \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory(
            $inventoryGeneratorFactory
        );
    }

    /**
     * Test for \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory::generate method
     */
    public function testGenerate()
    {
        $expectedResult = json_decode(
            '{"products":[{"entityId":null,"magentoId":null,"inventory":{"qty":0,'
            . '"configuration":{"status":null,"manageStock":null,"threshold":null,"displayOutOfStock":"0"}}}]}',
            true
        );
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->inventoryGenerator->expects($this->atLeastOnce())->method('generateInventory')->with([$productMock], 1)
            ->willReturn($expectedResult);

        $result = $this->inventory->generate([$productMock], 1);
        $this->assertEquals($expectedResult, $result);
    }
}
