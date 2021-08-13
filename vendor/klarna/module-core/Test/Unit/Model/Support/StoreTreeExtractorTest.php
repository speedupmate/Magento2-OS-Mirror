<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Model\Support;

use Klarna\Core\Model\Support\StoreTreeExtractor;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Model\Support\StoreTreeExtractor
 */
class StoreTreeExtractorTest extends TestCase
{
    /**
     * @var StoreTreeExtractor
     */
    private $model;
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getTree()
     */
    public function testGetTreeNoWebsiteAvailableReturnsEmptyArray(): void
    {
        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([]);

        $result = $this->model->getTree();

        static::assertSame([], $result);
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeNoGroupAvailableReturnsEmptyArray(): void
    {
        $websiteMock = $this->mockFactory->create(Website::class);
        $websiteMock
            ->method('getGroups')
            ->willReturn([]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock
            ]);

        $result = $this->model->getTree();

        static::assertSame([], $result);
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeNoStoreAvailableReturnsEmptyArray(): void
    {
        $groupMock = $this->mockFactory->create(Group::class);
        $groupMock
            ->method('getStores')
            ->willReturn([]);

        $websiteMock = $this->mockFactory->create(Website::class);
        $websiteMock
            ->method('getGroups')
            ->willReturn([
                1 => $groupMock
            ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock
            ]);

        $result = $this->model->getTree();

        static::assertSame([], $result);
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeWithOneEntry(): void
    {
        $storeMock = $this->createStoreMock('1', 'store_code');
        $groupMock = $this->createGroupMock('1', 'group_code', [
            1 => $storeMock
        ]);
        $websiteMock = $this->createWebsiteMock('1', 'website_code', [
            1 => $groupMock
        ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock
            ]);

        $result = $this->model->getTree();

        static::assertSame([
            'website_code' => [
                'data' => ['website_id' => '1'],
                'children' => [
                    'group_code' => [
                        'data' => ['group_id' => '1'],
                        'children' => [
                            'store_code' => [
                                'data' => ['store_id' => '1'],
                                'children' => [],
                            ]
                        ],
                    ]
                ],
            ]
        ], $result);
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeTwoStoresPresentInTree(): void
    {
        $storeMock = $this->createStoreMock('1', 'store_code');
        $storeMock2 = $this->createStoreMock('2', 'store_code_2');

        $groupMock = $this->createGroupMock('1', 'group_code', [
            1 => $storeMock,
            2 => $storeMock2
        ]);

        $websiteMock = $this->createWebsiteMock('1', 'website_code', [
            1 => $groupMock
        ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock
            ]);

        $result = $this->model->getTree();

        static::assertArrayHasKey(
            'store_code',
            $result['website_code']['children']['group_code']['children']
        );
        static::assertArrayHasKey(
            'store_code_2',
            $result['website_code']['children']['group_code']['children']
        );
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeTwoGroupsPresentInTree(): void
    {
        $storeMock = $this->createStoreMock('1', 'store_code');

        $groupMock = $this->createGroupMock('1', 'group_code', [
            1 => $storeMock,
        ]);

        $groupMock2 = $this->createGroupMock('2', 'group_code_2', [
            1 => $storeMock,
        ]);

        $websiteMock = $this->createWebsiteMock('1', 'website_code', [
            1 => $groupMock,
            2 => $groupMock2
        ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock
            ]);

        $result = $this->model->getTree();

        static::assertArrayHasKey(
            'group_code',
            $result['website_code']['children']
        );
        static::assertArrayHasKey(
            'group_code_2',
            $result['website_code']['children']
        );
    }

    /**
     * @covers ::getTree()
     */
    public function testGetTreeTwoWebsitesPresentInTree(): void
    {
        $storeMock = $this->createStoreMock('1', 'store_code');

        $groupMock = $this->createGroupMock('1', 'group_code', [
            1 => $storeMock,
        ]);

        $websiteMock = $this->createWebsiteMock('1', 'website_code', [
            1 => $groupMock,
        ]);

        $websiteMock2 = $this->createWebsiteMock('2', 'website_code_2', [
            1 => $groupMock,
        ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => $websiteMock,
                2 => $websiteMock2,
            ]);

        $result = $this->model->getTree();

        static::assertArrayHasKey(
            'website_code',
            $result
        );
        static::assertArrayHasKey(
            'website_code_2',
            $result
        );
    }

    private function createStoreMock($id, $code): MockObject
    {
        $storeMock = $this->mockFactory->create(Store::class);
        $storeMock
            ->method('getData')
            ->willReturn([
                'store_id' => $id
            ]);
        $storeMock
            ->method('getCode')
            ->willReturn($code);

        return $storeMock;
    }

    private function createGroupMock($id, $code, $returnStores = []): MockObject
    {
        $groupMock = $this->mockFactory->create(Group::class);
        $groupMock
            ->method('getStores')
            ->willReturn($returnStores);
        $groupMock
            ->method('getData')
            ->willReturn([
                'group_id' => $id
            ]);
        $groupMock
            ->method('getCode')
            ->willReturn($code);

        return $groupMock;
    }

    private function createWebsiteMock($id, $code, $returnGroups = []): MockObject
    {
        $websiteMock = $this->mockFactory->create(Website::class);
        $websiteMock
            ->method('getGroups')
            ->willReturn($returnGroups);
        $websiteMock
            ->method('getData')
            ->willReturn([
                'website_id' => $id
            ]);
        $websiteMock
            ->method('getCode')
            ->willReturn($code);

        return $websiteMock;
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory();
        $objectFactory         = new TestObjectFactory($this->mockFactory);
        $this->model           = $objectFactory->create(StoreTreeExtractor::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
