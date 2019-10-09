<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator\Attribute;

use \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AdditionalAttributes;

/**
 * Class AdditionalAttributesTest
 */
class AdditionalAttributesTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AdditionalAttributes
     */
    private $additionalAttributes;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    private $urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlFactory
     */
    private $urlFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $collectionMock;

    public function setUp()
    {
        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlMock->expects($this->any())->method('setScope')->willReturnSelf();
        $this->urlMock->expects($this->any())->method('getUrl')->willReturn('');
        $this->urlFactoryMock = $this->getMockBuilder(\Magento\Framework\UrlFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlFactoryMock->expects($this->any())->method('create')
            ->willReturn($this->urlMock);
        $this->collectionFactoryMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->collectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->collectionMock);
        $this->urlFinderMock = $this->getMockBuilder(\Magento\UrlRewrite\Model\UrlFinderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->additionalAttributes = new AdditionalAttributes(
            $this->urlFactoryMock,
            $this->collectionFactoryMock,
            $this->urlFinderMock
        );
    }

    public function testGetAttributes()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->urlFinderMock->expects($this->once())->method('findOneByData')
            ->willReturn(null);
        $result = $this->additionalAttributes->getAttributes($productMock, $storeMock);
        $this->assertEquals([
            'product_url' => ['' => ['value' => '']],
            'image_url' => ['' => ['value' => '']],
            'category_name' => ['' => ['value' => '']]
        ], $result);
    }
}
