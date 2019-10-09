<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer\PayloadGenerator;

/**
 * Class AttributeTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute
     */
    private $attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AdditionalAttributes
     */
    private $additionalAttributesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AttributeRawValueRetriever
     */
    private $attributeRawValueRetriever;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->additionalAttributesMock = $this->getMockBuilder(
            \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AdditionalAttributes::class
        )->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->configurableTypeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        )->disableOriginalConstructor()->getMock();
        $this->attributeRawValueRetriever = $this->getMockBuilder(
            \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AttributeRawValueRetriever::class
        )->disableOriginalConstructor()->getMock();

        $this->attribute = new \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute(
            $this->loggerMock,
            $this->storeManagerMock,
            [$this->additionalAttributesMock],
            $this->configurableTypeMock,
            $this->productRepositoryMock,
            $this->attributeRawValueRetriever
        );
    }

    public function testGenerate()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()->getMock();
        $storeMock->expects($this->any())->method('getCode')->willReturn('admin');
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')->willReturn([$storeMock]);
        /** @var \PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->once())
            ->method('getAttributes')->willReturn([$attributeMock]);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')->willReturn('sku');
        $resourceMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->atLeast(1))->method('getResource')->willReturn($resourceMock);
        $this->attributeRawValueRetriever->expects($this->atLeast(1))->method('getAttributeRawValue')
            ->willReturn(['sku' => 'value']);
        $frontendMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend::class)
            ->disableOriginalConstructor()->getMock();
        $attributeMock->expects($this->once())->method('getFrontend')->willReturn($frontendMock);
        $result = $this->attribute->generate([$productMock], 1);
        $this->assertEquals(
            json_decode(
                '{"": {"entityId":"value","magentoId":null,"attributes":{"sku":{"admin":{"value":"value"}}}}}',
                true
            ),
            $result
        );
    }
}
