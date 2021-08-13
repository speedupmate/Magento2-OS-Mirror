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

use Klarna\Core\Model\Support\InfoExtractor;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Model\Support\InfoExtractor
 */
class InfoExtractorTest extends TestCase
{
    /**
     * @var InfoExtractor
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getKlarnaInfo()
     */
    public function testGetKlarnaInfoPassesArgumentsCorrectly(): void
    {
        $this
            ->dependencyMocks['configExtractor']
            ->method('getConfig')
            ->willReturnCallback(function ($contains, $notContains, $scope, $id) {
                if ($scope === 'group') {
                    return [];
                }
                return [
                    'contains' => join(',', $contains),
                    'notContains' => join(',', $notContains),
                    'scope' => $scope,
                    'id' => $id,
                ];
            });

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => null // the values can be null, as we only use the ids to retrieve the configs
            ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getStores')
            ->willReturn([
                5 => null
            ]);

        $result = $this->model->getKlarnaInfo();

        static::assertSame([
            'klarna_default' => [
                'contains' => 'klarna',
                'notContains' => 'shared_secret',
                'scope' => 'default',
                'id' => null,
            ],
            'klarna_website_1' => [
                'contains' => 'klarna',
                'notContains' => 'shared_secret',
                'scope' => 'website',
                'id' => 1,
            ],
            'klarna_store_5' => [
                'contains' => 'klarna',
                'notContains' => 'shared_secret',
                'scope' => 'store',
                'id' => 5,
            ],
        ], $result);
    }

    /**
     * @covers ::getKlarnaInfo()
     */
    public function testGetKlarnaInfoWithMultipleEntriesPerScope(): void
    {
        $this->prepareMultipleScopeEntriesTest();

        $result = $this->model->getKlarnaInfo();

        static::assertSame([
            'klarna_default' => [
                'default' => null,
            ],
            'klarna_website_1' => [
                'website' => 1,
            ],
            'klarna_website_2' => [
                'website' => 2,
            ],
            'klarna_store_3' => [
                'store' => 3,
            ],
            'klarna_store_4' => [
                'store' => 4,
            ],
        ], $result);
    }

    private function prepareMultipleScopeEntriesTest(): void
    {
        $this
            ->dependencyMocks['configExtractor']
            ->method('getConfig')
            ->willReturnCallback(function ($contains, $notContains, $scope, $id) {
                return [
                    $scope => $id,
                ];
            });

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                1 => null, // the values can be null, as we only use the ids to retrieve the configs,
                2 => null
            ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getStores')
            ->willReturn([
                3 => null,
                4 => null
            ]);
    }

    /**
     * @covers ::getTaxInfo()
     */
    public function testGetTaxInfoPassesArgumentsCorrectly(): void
    {
        $this
            ->dependencyMocks['configExtractor']
            ->method('getConfig')
            ->willReturnCallback(function ($contains, $notContains, $scope, $id) {
                return [
                    'contains' => join(',', $contains),
                    'notContains' => join(',', $notContains),
                    'scope' => $scope,
                    'id' => $id,
                ];
            });

        $this
            ->dependencyMocks['storeManager']
            ->method('getWebsites')
            ->willReturn([
                7 => null // the values can be null, as we only use the ids to retrieve the configs
            ]);

        $this
            ->dependencyMocks['storeManager']
            ->method('getStores')
            ->willReturn([
                9 => null
            ]);

        $result = $this->model->getTaxInfo();

        static::assertSame([
            'tax_default' => [
                'contains' => 'tax',
                'notContains' => '',
                'scope' => 'default',
                'id' => null,
            ],
            'tax_website_7' => [
                'contains' => 'tax',
                'notContains' => '',
                'scope' => 'website',
                'id' => 7,
            ],
            'tax_store_9' => [
                'contains' => 'tax',
                'notContains' => '',
                'scope' => 'store',
                'id' => 9,
            ],
        ], $result);
    }

    /**
     * @covers ::getTaxInfo()
     */
    public function testGetTaxInfoWithMultipleEntriesPerScope(): void
    {
        $this->prepareMultipleScopeEntriesTest();

        $result = $this->model->getTaxInfo();

        static::assertSame([
            'tax_default' => [
                'default' => null,
            ],
            'tax_website_1' => [
                'website' => 1,
            ],
            'tax_website_2' => [
                'website' => 2,
            ],
            'tax_store_3' => [
                'store' => 3,
            ],
            'tax_store_4' => [
                'store' => 4,
            ],
        ], $result);
    }

    /**
     * @covers ::getStoreTreeInfo()
     */
    public function testGetStoreTreeInfoPassesThroughResult(): void
    {
        $this
            ->dependencyMocks['storeTreeExtractor']
            ->method('getTree')
            ->willReturn([
                'treeKey' => 'treeValue'
            ]);

        $result = $this->model->getStoreTreeInfo();

        static::assertSame([
            'treeKey' => 'treeValue'
        ], $result);
    }

    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->model           = $objectFactory->create(InfoExtractor::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
