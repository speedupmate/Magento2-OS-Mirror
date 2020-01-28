<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator\Attribute;

use \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\ConfigurableAttributes;

/**
 * Class ConfigurableAttributesTest
 */
class ConfigurableAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\ConfigurableAttributes
     */
    private $configurableAttributes;

    public function setUp()
    {
        $this->configurableAttributes = new ConfigurableAttributes();
    }

    public function testGetAttributes()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();
        $result = $this->configurableAttributes->getAttributes($productMock, $storeMock, $productMock);
        $this->assertEquals([
            'item_group_id' => ['' => ['value' => null]],
        ], $result);
    }
}
