<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Core\Tests\Unit\Model;

use Klarna\Core\Api\OrderInterface as KlarnaOrder;
use Klarna\Core\Model\MerchantPortal;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Sales\Api\Data\OrderInterface as MageOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\Store;

/**
 * @coversDefaultClass Klarna\Core\Model\MerchantPortal
 */
class MerchantPortalTest extends TestCase
{
    /**
     * @var MerchantPortal
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var MockObject|KlarnaOrder
     */
    private $klarnaOrder;
    /**
     * @var MockObject|MageOrder
     */
    private $mageOrder;

    /**
     * @covers ::getOrderMerchantPortalLink
     */
    public function testGetOrderMerchantPortalLinkWithEuApiVersion(): void
    {
        $merchantId = 'K1';
        $this->dependencyMocks['configHelper']->method('getApiConfig')
            ->willReturnCallback(function ($field) use ($merchantId) {
               if ($field === 'api_version') {
                   return 'eu';
               }
               return $merchantId; // Merchant ID
            });

        $urlPath = 'merchants/' . $merchantId . '/orders/';
        $result  = $this->model->getOrderMerchantPortalLink($this->mageOrder, $this->klarnaOrder);
        $expected = MerchantPortal::MERCHANT_PORTAL_EU . $urlPath;

        static::assertEquals($result, $expected);
    }

    /**
     * @covers ::getOrderMerchantPortalLink
     */
    public function testGetOrderMerchantPortalLinkWithNaApiVersion(): void
    {
        $merchantId = 'N1';
        $this->dependencyMocks['configHelper']->method('getApiConfig')
            ->willReturnCallback(function ($field) use ($merchantId) {
                if ($field === 'api_version') {
                    return 'na';
                }
                return $merchantId; // Merchant ID
            });

        $urlPath = 'merchants/' . $merchantId . '/orders/';
        $result  = $this->model->getOrderMerchantPortalLink($this->mageOrder, $this->klarnaOrder);
        $expected = MerchantPortal::MERCHANT_PORTAL_US . $urlPath;

        static::assertEquals($result, $expected);
    }

    /**
     * @covers ::getOrderMerchantPortalLink
     */
    public function testGetOrderMerchantPortalLinkWithMerchantUsername(): void
    {
        $merchantId       = 'K1';
        $merchantUsername = 'K1_b62k12';
        $this->dependencyMocks['configHelper']->method('getApiConfig')
            ->willReturnCallback(function ($field) use ($merchantUsername) {
                if ($field === 'api_version') {
                    return 'eu';
                }
                return $merchantUsername; // Merchant ID
            });

        $urlPath = 'merchants/' . $merchantId . '/orders/';
        $result  = $this->model->getOrderMerchantPortalLink($this->mageOrder, $this->klarnaOrder);
        $expected = MerchantPortal::MERCHANT_PORTAL_EU . $urlPath;

        static::assertEquals($result, $expected);
    }

    protected function setUp()
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->model           = $objectFactory->create(MerchantPortal::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
        $this->klarnaOrder     = $mockFactory->create(KlarnaOrder::class);
        $this->mageOrder       = $this->getMockBuilder(MageOrder::class)
                                    ->setMethods(['getStore'])
                                    ->getMockForAbstractClass();

        $store = $mockFactory->create(Store::class);
        $this->mageOrder->method('getStore')
            ->willReturn($store);
    }
}
