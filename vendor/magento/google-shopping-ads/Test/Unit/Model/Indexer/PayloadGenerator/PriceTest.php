<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator;

/**
 * Class PriceTest
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Price
     */
    private $price;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerMock;

    public function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->price = new \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Price(
            $this->storeManagerMock
        );
    }

    public function testGenerate()
    {
        $this->storeManagerMock->expects($this->once())->method('getWebsite')
            ->willReturn(
                $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
                    ->disableOriginalConstructor()->getMock()
            );
        $this->storeManagerMock->expects($this->once())->method('getGroup')
            ->willReturn(
                $this->getMockBuilder(\Magento\Store\Api\Data\GroupInterface::class)
                    ->disableOriginalConstructor()->getMock()
            );
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $this->storeManagerMock->expects($this->exactly(2))->method('getStore')
            ->willReturn($storeMock);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->any())->method('getPriceInfo')->willReturn($priceInfoMock);
        $priceMock = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()->getMock();
        $priceInfoMock->expects($this->any())->method('getPrice')->willReturn($priceMock);
        $adjustmentMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class)
            ->disableOriginalConstructor()->getMock();
        $priceInfoMock->expects($this->any())->method('getAdjustment')->willReturn($adjustmentMock);

        $result = $this->price->generate([$productMock], 1);
        $this->assertEquals(
            json_decode(
                '{"":{"entityId":null,"magentoId":null,"prices":{"regularPrice":{"amount":0,"currency":'
                . '"USD","adjustment":{"amount":0,"currency":"USD"}},"specialPrice":{"amount":0,"currency":"USD",'
                . '"adjustment":{"amount":0,"currency":"USD"}}}}}',
                true
            ),
            $result
        );
    }
}
