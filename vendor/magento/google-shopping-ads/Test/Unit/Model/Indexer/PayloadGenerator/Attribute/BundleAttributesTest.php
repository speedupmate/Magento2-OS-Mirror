<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator\Attribute;

/**
 * Class BundleAttributesTest
 */
class BundleAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\BundleAttributes
     */
    private $bundleAttributes;

    public function setUp()
    {
        $this->bundleAttributes =
            new \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\BundleAttributes();
    }

    public function testGetAttributes()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $result = $this->bundleAttributes->getAttributes($productMock, $storeMock);
        $this->assertEquals([
            'is_bundle' => ['' => ['value' => true]],
        ], $result);
    }
}
