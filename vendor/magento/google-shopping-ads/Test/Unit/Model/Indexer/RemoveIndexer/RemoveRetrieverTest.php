<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\RemoveIndexer;

/**
 * Class RemoveRetrieverTest
 */
class RemoveRetrieverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever
     */
    private $removeRetriever;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableTypeMock;

    public function setUp()
    {
        $this->collectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->collectionFactoryMock->expects($this->any())->method('create')
            ->willReturn($this->collectionMock);
        $this->configurableTypeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        )->disableOriginalConstructor()->getMock();
        $this->removeRetriever = new \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever(
            $this->collectionFactoryMock,
            $this->configurableTypeMock
        );
    }

    public function testGetRemovedIds()
    {
        $this->collectionMock->expects($this->atLeast(1))
            ->method('getAllIds')
            ->willReturnOnConsecutiveCalls(
                [1, 2, 3, 4],
                [1, 2, 3],
                [3, 4, 5],
                [7, 8],
                [21, 22],
                [30]
            );
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->atLeast(1))
            ->method('getTypeId')
            ->willReturnOnConsecutiveCalls(
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                null
            );
        $this->collectionMock->expects($this->atLeast(1))
            ->method('getItems')
            ->willReturnOnConsecutiveCalls(
                [$productMock],
                [$productMock, $productMock]
            );

        $this->configurableTypeMock->expects($this->atLeast(1))
            ->method('getUsedProducts')
            ->willReturnOnConsecutiveCalls(
                [$productMock],
                [$productMock]
            );

        $result = $this->removeRetriever->getRemovedIds(1, [1, 2, 3, 4, 5]);
        $expected = [5, 4, 2, 7, 8, null, 21, 22, null, 30];
        unset($expected[2]);
        unset($expected[8]);
        $this->assertEquals($expected, $result);
    }
}
