<?php

namespace Klarna\Onsitemessaging\Block;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Product
 */
class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProduct(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(0))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/enabled',
                'store',
                'base'
            )
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(1))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/product_enabled',
                'store',
                'base'
            )
            ->willReturn(true);
        $this->assertTrue($this->model->showOnProduct());
    }

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProductReturnsFalseWhenOsmDisabled(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(0))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/enabled',
                'store',
                'base'
            )
            ->willReturn(false);
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(1))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/product_enabled',
                'store',
                'base'
            )
            ->willReturn(true);
        $this->assertFalse($this->model->showOnProduct());
    }

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProductReturnsFalseWhenProductDisabled(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(0))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/enabled',
                'store',
                'base'
            )
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->expects($this->at(1))
            ->method('isSetFlag')
            ->with(
                'klarna/osm/product_enabled',
                'store',
                'base'
            )
            ->willReturn(false);
        $this->assertFalse($this->model->showOnProduct());
    }

    /**
     * @covers ::getLocale
     */
    public function testGetLocale(): void
    {
        $this->dependencyMocks['locale']
            ->method('getLocale')
            ->willReturn('en_US');
        $this->assertEquals('en-US', $this->model->getLocale());
    }

    /**
     * @covers ::getPlacementId
     */
    public function testGetPlacementId(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('SOME-ID');

        $this->assertEquals('SOME-ID', $this->model->getPlacementId());
    }

    /**
     * @covers ::getTheme
     */
    public function testGetTheme(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('default');

        $this->assertEquals('default', $this->model->getTheme());
    }

    /**
     * @covers ::getPurchaseAmount
     */
    public function testGetPurchaseAmount(): void
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->dependencyMocks['productHelper']
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->method('getQty')->willReturn(1);
        $productMock->method('getFinalPrice')->willReturn(123.45);

        $this->assertEquals(12345, $this->model->getPurchaseAmount());
    }

    protected function setUp(): void
    {
        $mockFactory   = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);
        $storeManager   = $mockFactory->create(
            StoreManagerInterface::class,
            [
                'setIsSingleStoreModeAllowed',
                'hasSingleStore',
                'isSingleStoreMode',
                'getStore',
                'getStores',
                'getWebsite',
                'getWebsites',
                'reinitStores',
                'getDefaultStoreView',
                'getGroup',
                'getGroups',
                'setCurrentStore'
            ]
        );
        $scopeConfig   = $mockFactory->create(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $context       = $mockFactory->create(Context::class, ['getScopeConfig', 'getStoreManager']);
        $context->method('getScopeConfig')->willReturn($scopeConfig);
        $context->method('getStoreManager')->willReturn($storeManager);
        $this->model                           = $objectFactory->create(
            Product::class,
            [
                Context::class => ['getScopeConfig', 'getStoreManager']
            ],
            [
                Context::class => $context
            ]
        );
        $this->dependencyMocks                 = $objectFactory->getDependencyMocks();
        $this->dependencyMocks['_scopeConfig'] = $scopeConfig;
        $this->dependencyMocks['_storeManager'] = $storeManager;
        $this->dependencyMocks['_storeManager']->method('getStore')->willReturn('base');
    }
}
