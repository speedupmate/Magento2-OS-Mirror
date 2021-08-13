<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Cron;

use Klarna\Core\Cron\UpdateApiLog;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Api\SearchResults;
use PHPUnit\Framework\TestCase;
use Klarna\Core\Model\Log;

/**
 * @coversDefaultClass \Klarna\Core\Cron\UpdateApiLog
 */
class UpdateApiLogTest extends TestCase
{
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var UpdateApiLog
     */
    private $updateApiLog;

    /**
     * @covers ::execute()
     * @doesNotPerformAssertions
     */
    public function testExecuteUpdateModel(): void
    {
        $searchCriteria = $this->mockFactory->create(SearchCriteria::class);
        $this->dependencyMocks['searchCriteriaBuilder']->method('create')
            ->willReturn($searchCriteria);

        $searchResults = $this->mockFactory->create(SearchResults::class);
        $searchResultsItem = $this->mockFactory->create(Log::class, []);
        $searchResultsItem->method('getKlarnaId')
            ->willReturn('my_klarna_id');
        $searchResultsItem->method('getIncrementId')
            ->willReturn('my_increment_id');
        $searchResults->method('getItems')
            ->willReturn([$searchResultsItem]);
        $this->dependencyMocks['logRepository']->method('getListGroupedByKlarnaId')
            ->willReturn($searchResults);

        $searchResultsUpdate = $this->mockFactory->create(SearchResults::class);
        $searchResultsItemUpdate = $this->mockFactory->create(Log::class, []);
        $searchResultsItemUpdate->method('getKlarnaId')
            ->willReturn('my_klarna_id');
        $searchResultsItemUpdate->method('setIncrementId')
            ->with('my_increment_id');
        $this->dependencyMocks['logRepository']->method('save')
            ->with($searchResultsItemUpdate);
        $searchResultsUpdate->method('getItems')
            ->willReturn([$searchResultsItemUpdate]);
        $this->dependencyMocks['logRepository']->method('getList')
            ->willReturn($searchResultsUpdate);

        $this->updateApiLog->execute();
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory();
        $objectFactory         = new TestObjectFactory($this->mockFactory);
        $this->updateApiLog    = $objectFactory->create(UpdateApiLog::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}