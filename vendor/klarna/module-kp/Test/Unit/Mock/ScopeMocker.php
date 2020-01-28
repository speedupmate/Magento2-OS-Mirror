<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Test\Unit\Mock;

use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class that adds preconfigured mock creation for specific use-cases that cross multiple test classes
 *
 * @package Klarna\Kp\Test\Unit\Mock
 */
class ScopeMocker extends TestCase
{
    /**
     * Creates a mock Store object and returns it
     *
     * @param string $storeName
     * @param string $storeCode
     * @param string $websiteName
     * @return Store|MockObject
     */
    public function createStoreMock($storeName = 'Default', $storeCode = 'default', $websiteName = 'Default')
    {
        $websiteMock = $this->createWebsiteMock($websiteName);
        return $this->createStoreMockUsingWebsite($websiteMock, $storeName, $storeCode);
    }

    /**
     * Creates a mock Website object and returns it
     *
     * @param string $websiteName
     * @return Website|MockObject
     */
    public function createWebsiteMock($websiteName = 'Default')
    {
        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getName')->willReturn($websiteName);
        return $websiteMock;
    }

    /**
     * Creates a store mock using a provided Website mock
     *
     * @param Website|MockObject $websiteMock
     * @param string             $storeName
     * @param string             $storeCode
     * @return Store|MockObject
     */
    public function createStoreMockUsingWebsite($websiteMock, $storeName = 'Default', $storeCode = 'default')
    {
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getCode')->willReturn($storeCode);
        $storeMock->method('getWebsite')->willReturn($websiteMock);
        $storeMock->method('getName')->willReturn($storeName);
        return $storeMock;
    }
}
