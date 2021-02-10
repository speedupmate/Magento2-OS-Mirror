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
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Header
 */
class HeaderTest extends TestCase
{
    /**
     * @var Header
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::isOsmEnabled
     */
    public function testIsOsmEnabledReturnsFalseWhenDisabled(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(false);
        $this->assertFalse($this->model->isOsmEnabled());
    }

    /**
     * @covers ::getDataId
     */
    public function testGetDataId(): void
    {
        $value = 'SOME-VALUE';
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn($value);
        $this->assertEquals($value, $this->model->getDataId());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlHandlesForNoVersion(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn(null);
        $this->assertEquals('https://eu-library.playground.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsNaPlayground(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_na');
        $this->assertEquals('https://na-library.playground.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsNaProduction(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(false);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_na');
        $this->assertEquals('https://na-library.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsOcPlayground(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_oc');
        $this->assertEquals('https://oc-library.playground.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsOcProduction(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(false);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_oc');
        $this->assertEquals('https://oc-library.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsEuPlayground(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(true);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_eu');
        $this->assertEquals('https://eu-library.playground.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::getEndpoint
     * @covers ::getJsUrl
     */
    public function testGetJsUrlReturnsEuProduction(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(false);
        $this->dependencyMocks['_scopeConfig']
            ->method('getValue')
            ->willReturn('kp_eu');
        $this->assertEquals('https://eu-library.klarnaservices.com/lib.js', $this->model->getJsUrl());
    }

    /**
     * @covers ::isOsmEnabled
     */
    public function testIsOsmEnabled(): void
    {
        $this->dependencyMocks['_scopeConfig']
            ->method('isSetFlag')
            ->willReturn(true);
        $this->assertTrue($this->model->isOsmEnabled());
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
            Header::class,
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
