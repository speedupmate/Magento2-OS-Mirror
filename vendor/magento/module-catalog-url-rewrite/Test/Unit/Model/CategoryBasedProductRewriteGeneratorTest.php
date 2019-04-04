<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;

/**
 * Tests CategoryBasedProductRewriteGenerator class.
 */
class CategoryBasedProductRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Product rewrite generator for scope Mock.
     *
     * @var ProductScopeRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productScopeRewriteGeneratorMock;

    /**
     * Category based product rewrite generator.
     * @var CategoryBasedProductRewriteGenerator
     */
    private $generator;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->productScopeRewriteGeneratorMock = $this->getMockBuilder(ProductScopeRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generator = new CategoryBasedProductRewriteGenerator(
            $this->productScopeRewriteGeneratorMock
        );
    }

    /**
     * Covers generate() with global scope.
     *
     * @return void
     */
    public function testGenerationWithGlobalScope()
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $categoryId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(true);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForGlobalScope')
            ->with([$categoryMock], $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryMock, $categoryId));
    }

    /**
     * Covers generate() with specific store.
     *
     * @return void
     */
    public function testGenerationWithSpecificStore()
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeId = 1;
        $categoryId = 1;
        $urls = ['dummy-url.html'];

        $productMock->expects($this->once())
            ->method('getVisibility')
            ->willReturn(2);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('isGlobalScope')
            ->with($storeId)
            ->willReturn(false);
        $this->productScopeRewriteGeneratorMock->expects($this->once())
            ->method('generateForSpecificStoreView')
            ->with($storeId, [$categoryMock], $productMock, $categoryId)
            ->willReturn($urls);

        $this->assertEquals($urls, $this->generator->generate($productMock, $categoryMock, $categoryId));
    }
}
