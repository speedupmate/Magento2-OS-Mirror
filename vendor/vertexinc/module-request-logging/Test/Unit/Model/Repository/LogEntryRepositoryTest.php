<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Test\Unit\Model\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterfaceFactory;
use Vertex\RequestLogging\Model\Data\LogEntry;
use Vertex\RequestLogging\Model\Data\LogEntryFactory;
use Vertex\RequestLogging\Model\Repository\LogEntryRepository;
use Vertex\RequestLogging\Model\Repository\LogEntrySearchResult;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry as LogEntryResource;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry\Collection;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry\CollectionFactory;
use Vertex\RequestLogging\Test\Unit\TestCase;

class LogEntryRepositoryTest extends TestCase
{
    /**
     * @covers LogEntryRepository::__construct()
     */
    public function testConstructorThrowsNoErrors(): void
    {
        $this->getObject(LogEntryRepository::class);
    }

    /**
     * @return LogEntryFactory
     */
    public function createLogEntryFactory(): LogEntryFactory
    {
        $factory = $this->createMock(LogEntryFactory::class);
        $factory->method('create')
            ->willReturnCallback(
                function () {
                    return $this->getObject(LogEntry::class);
                }
            );
        return $factory;
    }

    /**
     * @return CollectionFactory
     */
    public function createCollectionFactory(): CollectionFactory
    {
        $factory = $this->createMock(CollectionFactory::class);
        $factory->method('create')
            ->willReturnCallback(
                function () {
                    return $this->getCollection(
                        Collection::class,
                        [
                        12
                        ]
                    );
                }
            );
        return $factory;
    }

    /**
     * @return LogEntrySearchResultsInterfaceFactory
     */
    public function createSearchResultsFactory(): LogEntrySearchResultsInterfaceFactory
    {
        $factory = $this->createMock(LogEntrySearchResultsInterfaceFactory::class);
        $factory->method('create')
            ->willReturnCallback(
                function () {
                    return $this->getObject(LogEntrySearchResult::class);
                }
            );
        return $factory;
    }

    /**
     * @param array $arguments
     * @return LogEntryRepository
     */
    public function createRepository($arguments = []): LogEntryRepository
    {
        return $this->getObject(
            LogEntryRepository::class,
            array_merge(
                [
                    'logEntryFactory' => $this->createLogEntryFactory(),
                    'collectionFactory' => $this->createCollectionFactory(),
                    'searchResultsFactory' => $this->createSearchResultsFactory(),
                ],
                $arguments
            )
        );
    }

    /**
     * @covers LogEntryRepository::save()
     * @covers LogEntryRepository::mapDataIntoModel()
     * @throws CouldNotSaveException
     */
    public function testModelPassedToResourceDuringSaveContainsSameData(): void
    {
        /** @var LogEntry $modelToSave */
        $modelToSave = $this->getObject(LogEntry::class);
        $modelToSave->setType(uniqid('type-'));
        $modelToSave->setOrderId(random_int(0, PHP_INT_MAX));
        $modelToSave->setTotalTax((float)(random_int(0, PHP_INT_MAX) / 100));
        $modelToSave->setTaxAreaId(random_int(0, PHP_INT_MAX));
        $modelToSave->setSubTotal((float)(random_int(0, PHP_INT_MAX) / 100));
        $modelToSave->setTotal((float)(random_int(0, PHP_INT_MAX) / 100));
        $modelToSave->setLookupResult(uniqid('lookup-result-'));
        $modelToSave->setDate(date('Y-m-d', random_int(strtotime('2015-01-01'), strtotime('2017-01-01'))));
        $modelToSave->setRequestXml(uniqid('request-xml-'));
        $modelToSave->setResponseXml(uniqid('response-xml-'));

        $resourceMock = $this->createMock(LogEntryResource::class);
        $resourceMock->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($parameter) use ($modelToSave) {
                        $this->assertEquals($modelToSave->getType(), $parameter->getType());
                        $this->assertEquals($modelToSave->getOrderId(), $parameter->getOrderId());
                        $this->assertEquals($modelToSave->getTotalTax(), $parameter->getTotalTax());
                        $this->assertEquals($modelToSave->getTaxAreaId(), $parameter->getTaxAreaId());
                        $this->assertEquals($modelToSave->getSubTotal(), $parameter->getSubTotal());
                        $this->assertEquals($modelToSave->getTotal(), $parameter->getTotal());
                        $this->assertEquals($modelToSave->getLookupResult(), $parameter->getLookupResult());
                        $this->assertEquals($modelToSave->getDate(), $parameter->getDate());
                        $this->assertEquals($modelToSave->getRequestXml(), $parameter->getRequestXml());
                        $this->assertEquals($modelToSave->getResponseXml(), $parameter->getResponseXml());
                        return true;
                    }
                )
            );

        $repository = $this->createRepository(['resourceModel' => $resourceMock]);

        $repository->save($modelToSave);
    }

    /**
     * @covers LogEntryRepository::deleteById()
     * @throws CouldNotDeleteException
     */
    public function testDeleteById(): void
    {
        $resourceModel = $this->createMock(LogEntryResource::class);
        $resourceModel->expects($this->once())
            ->method('delete')
            ->with(
                $this->callback(
                    function ($result) {
                        return $result->getId() == 12;
                    }
                )
            );

        /** @var LogEntryRepository $repository */
        $repository = $this->createRepository(['resourceModel' => $resourceModel]);

        $result = $repository->deleteById(12);
        $this->assertTrue($result);
    }

    /**
     * @covers LogEntryRepository::delete()
     * @throws CouldNotDeleteException
     */
    public function testDelete(): void
    {
        $logEntry = $this->getObject(LogEntry::class);
        $logEntry->setId(5);

        $resourceModel = $this->createMock(LogEntryResource::class);
        $resourceModel->expects($this->once())
            ->method('delete')
            ->with(
                $this->callback(
                    function ($result) {
                        return $result->getId() == 5;
                    }
                )
            );

        $repository = $this->createRepository(['resourceModel' => $resourceModel]);

        $result = $repository->delete($logEntry);
        $this->assertTrue($result);
    }

    /**
     * @covers LogEntryRepository::getList()
     */
    public function testGetListReturnsResultFromCollection(): void
    {
        $repository = $this->createRepository();
        $criteriaMock = $this->createMock(SearchCriteriaInterface::class);

        $result = $repository->getList($criteriaMock);

        $this->assertInstanceOf(SearchResultsInterface::class, $result);

        $items = $result->getItems();

        $this->assertCount(1, $items);
        $this->assertEquals(12, $items[0]);
    }
}
