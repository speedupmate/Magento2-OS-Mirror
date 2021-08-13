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
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Cart
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::showInCart
     */
    public function testShowInCart(): void
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
                'klarna/osm/cart_enabled',
                'store',
                'base'
            )
            ->willReturn(true);
        $this->assertTrue($this->model->showInCart());
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
     * @covers ::getPurchaseAmount
     */
    public function testGetPurchaseAmount(): void
    {
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getGrandTotal'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dependencyMocks['session']
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->method('getGrandTotal')->willReturn(123.45);

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
        $context->method('getStoreManager')->willReturn($storeManager);
        $context->method('getScopeConfig')->willReturn($scopeConfig);
        $this->model                           = $objectFactory->create(
            Cart::class,
            [
                Context::class => ['getScopeConfig', 'getStoreManager']
            ],
            [
                Context::class => $context
            ]
        );
        $this->dependencyMocks                  = $objectFactory->getDependencyMocks();
        $this->dependencyMocks['_storeManager'] = $storeManager;
        $this->dependencyMocks['_scopeConfig']  = $scopeConfig;
        $this->dependencyMocks['_storeManager']->method('getStore')->willReturn('base');
    }
}