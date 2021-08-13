<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Model;

use Klarna\Core\Api\Data\LogInterface;
use Klarna\Core\Model\Log;
use Klarna\Core\Model\LogRepository;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchCriteria;
use Klarna\Core\Model\ResourceModel\Log\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Filter;

/**
 * @coversDefaultClass \Klarna\Core\Model\LogRepository
 */
class LogRepositoryTest extends TestCase
{
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var Log
     */
    private $log;
    /**
     * @var LogInterface
     */
    private $logInterface;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var MockFactory
     */
    private $mockFactory;

    /**
     * @covers ::save()
     */
    public function testSave(): void
    {
        $actual = $this->logRepository->save($this->log);
        self::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::delete()
     */
    public function testDelete(): void
    {
        $actual = $this->logRepository->delete($this->log);
        self::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::deleteById()
     */
    public function testDeleteById(): void
    {
        $this->log->method('getId')->willReturn('1');
        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($this->log);
        $actual = $this->logRepository->deleteById('1');
        self::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::getById()
     */
    public function testGetById(): void
    {
        $this->log->method('getId')->willReturn('1');
        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($this->log);
        $actual = $this->logRepository->getById('1');
        self::assertInstanceOf(Log::class, $actual);
    }

    /**
     * @covers ::getList()
     */
    public function testGetListWithEmptyFilterGroups(): void
    {
        $searchResultInstance = $this->mockFactory->create(SearchResults::class);
        $this->dependencyMocks['searchResultsFactory']->method('create')
            ->willReturn($searchResultInstance);

        $collection = $this->mockFactory->create(Collection::class);
        $collection->method('getItems')
            ->willReturn([]);
        $this->dependencyMocks['logCollectionFactory']->method('create')
            ->willReturn($collection);

        $searchCriteria = $this->mockFactory->create(SearchCriteria::class);
        $searchCriteria->method('getFilterGroups')
            ->willReturn([]);
        $result = $this->logRepository->getList($searchCriteria);

        self::assertSame($searchResultInstance, $result);
    }

    /**
     * @covers ::getList()
     */
    public function testGetListGroupedByKlarnaIdWithFilledFilterGroups(): void
    {
        $searchResultInstance = $this->mockFactory->create(SearchResults::class);
        $this->dependencyMocks['searchResultsFactory']->method('create')
            ->willReturn($searchResultInstance);

        $collection = $this->mockFactory->create(Collection::class);
        $collection->method('getItems')
            ->willReturn([]);
        $selectInstance = $this->mockFactory->create(Select::class);
        $selectInstance->method('group')
            ->with('klarna_id');
        $collection->method('getSelect')
            ->willReturn($selectInstance);
        $this->dependencyMocks['logCollectionFactory']->method('create')
            ->willReturn($collection);

        $searchCriteria = $this->mockFactory->create(SearchCriteria::class);
        $searchCriteria->method('getFilterGroups')
            ->willReturn([]);
        $filterGroup = $this->mockFactory->create(FilterGroup::class);
        $filter = $this->mockFactory->create(Filter::class);
        $filterGroup->method('getFilters')
            ->willReturn([$filter]);
        $searchCriteria->method('getFilterGroups')
            ->willReturn([$filterGroup]);
        $result = $this->logRepository->getListGroupedByKlarnaId($searchCriteria);

        self::assertSame($searchResultInstance, $result);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory();
        $objectFactory         = new TestObjectFactory($this->mockFactory);
        $this->logRepository   = $objectFactory->create(LogRepository::class);
        $this->log             = $this->mockFactory->create(Log::class);
        $this->logInterface    = $this->mockFactory->create(LogInterface::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
