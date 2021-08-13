<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Ui\DataProvider;

use Klarna\Core\Ui\DataProvider\Logs;
use Klarna\Core\Model\Log;
use Klarna\Core\Model\ResourceModel\Log\Collection;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Core\Model\ResourceModel\Log\CollectionFactory;

/**
 * @coversDefaultClass \Klarna\Core\Ui\DataProvider\Logs
 */
class LogsTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Logs
     */
    private $dataProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getData()
     */
    public function testGetDataNoItemsExists(): void
    {
        $collectionFactory = $this->mockFactory->create(CollectionFactory::class);
        $collection = $this->mockFactory->create(Collection::class);
        $collection->method('getItems')
            ->willReturn([]);
        $collectionFactory->method('create')->willReturn($collection);

        $this->createModel([
            CollectionFactory::class => $collectionFactory
        ]);

        static::assertEmpty($this->dataProvider->getData());
    }

    /**
     * @covers ::getData()
     */
    public function testGetDataReturnExistingData(): void
    {
        $collectionFactory = $this->mockFactory->create(CollectionFactory::class);
        $collection = $this->mockFactory->create(Collection::class);

        $item = $this->mockFactory->create(Log::class);
        $item->method('getData')
            ->willReturn(
                [
                    'request' => json_encode(['order_amount' => 123]),
                    'response' => json_encode(['order_amount' => 123]),
                ]
            );
        $item->method('getId')
            ->willReturn(1);
        $collection->method('getItems')
            ->willReturn([$item]);
        $collectionFactory->method('create')->willReturn($collection);

        $this->createModel([
            CollectionFactory::class => $collectionFactory
        ]);

        static::assertNotEmpty($this->dataProvider->getData());
    }

    /**
     * @covers ::getData()
     */
    public function testGetDataReturnExistingResult(): void
    {
        $collectionFactory = $this->mockFactory->create(CollectionFactory::class);
        $collection = $this->mockFactory->create(Collection::class);

        $item = $this->mockFactory->create(Log::class);
        $item->method('getData')
            ->willReturn(
                [
                    'request' => json_encode(['order_amount' => 123]),
                    'response' => json_encode(['order_amount' => 123]),
                ]
            );
        $item->method('getId')
            ->willReturn(1);
        $collection->expects(static::once())
            ->method('getItems')
            ->willReturn([$item]);
        $collectionFactory->method('create')->willReturn($collection);

        $this->createModel([
            CollectionFactory::class => $collectionFactory
        ]);

        $resultFirstTry = $this->dataProvider->getData();
        static::assertSame($resultFirstTry, $this->dataProvider->getData());
    }

    protected function setUp(): void
    {
        $this->mockFactory = new MockFactory();
    }

    private function createModel(array $instances = []): void
    {
        $objectFactory         = new TestObjectFactory($this->mockFactory);
        $this->dataProvider    = $objectFactory->create(Logs::class, [], $instances);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
