<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterfaceFactory;
use Vertex\RequestLoggingApi\Api\LogEntryRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Vertex\RequestLogging\Model\Data\LogEntry;
use Vertex\RequestLogging\Model\Data\LogEntryFactory;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry as ResourceModel;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry\Collection;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry\CollectionFactory;

/**
 * Repository of Log Entries
 */
class LogEntryRepository implements LogEntryRepositoryInterface
{
    /** @var ResourceModel */
    private $resourceModel;

    /** @var LogEntryFactory */
    private $logEntryFactory;

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var LogEntrySearchResultsInterfaceFactory */
    private $searchResultsFactory;

    /** @var CollectionProcessorInterface */
    private $collectionProcessor;

    /**
     * @param ResourceModel $resourceModel
     * @param LogEntryFactory $logEntryFactory
     * @param CollectionFactory $collectionFactory
     * @param LogEntrySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        LogEntryFactory $logEntryFactory,
        CollectionFactory $collectionFactory,
        LogEntrySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resourceModel = $resourceModel;
        $this->logEntryFactory = $logEntryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(LogEntryInterface $logEntry): LogEntryRepositoryInterface
    {
        $model = $this->mapDataIntoModel($logEntry);
        try {
            $this->resourceModel->save($model);
        } catch (\Exception $originalException) {
            throw new CouldNotSaveException(__('Could not save Log Entry'), $originalException);
        }

        return $this;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): LogEntrySearchResultsInterface
    {
        /** @var LogEntrySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());

        $logEntries = [];
        /** @var LogEntry $logEntryModel */
        foreach ($collection as $logEntryModel) {
            $logEntries[] = $logEntryModel;
        }
        $searchResults->setItems($logEntries);
        return $searchResults;
    }

    public function delete(LogEntryInterface $logEntry): bool
    {
        return $this->deleteById($logEntry->getId());
    }

    public function deleteById(int $logEntryId): bool
    {
        /** @var LogEntry $model */
        $model = $this->logEntryFactory->create();
        $model->setId($logEntryId);
        try {
            $this->resourceModel->delete($model);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete log entry'), $e);
        }

        return true;
    }

    public function deleteByCriteria(SearchCriteriaInterface $searchCriteria): bool
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        try {
            $this->resourceModel->deleteByCollection($collection);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete log entries'), $e);
        }

        return true;
    }

    /**
     * Convert a LogEntryInterface into a LogEntry model
     *
     * @param LogEntryInterface $logEntry
     * @return LogEntry
     */
    private function mapDataIntoModel(LogEntryInterface $logEntry): LogEntry
    {
        /** @var LogEntry $model */
        $model = $this->logEntryFactory->create();
        $model->setData(
            [
                LogEntryInterface::FIELD_ID => $logEntry->getId(),
                LogEntryInterface::FIELD_TYPE => $logEntry->getType(),
                LogEntryInterface::FIELD_ORDER_ID => $logEntry->getOrderId(),
                LogEntryInterface::FIELD_TOTAL_TAX => $logEntry->getTotalTax(),
                LogEntryInterface::FIELD_TAX_AREA_ID => $logEntry->getTaxAreaId(),
                LogEntryInterface::FIELD_SUBTOTAL => $logEntry->getSubTotal(),
                LogEntryInterface::FIELD_TOTAL => $logEntry->getTotal(),
                LogEntryInterface::FIELD_LOOKUP_RESULT => $logEntry->getLookupResult(),
                LogEntryInterface::FIELD_MODULE_NAME => $logEntry->getModuleName(),
                LogEntryInterface::FIELD_MODULE_VERSION => $logEntry->getModuleVersion(),
                LogEntryInterface::FIELD_REQUEST_DATE => $logEntry->getDate(),
                LogEntryInterface::FIELD_REQUEST_XML => $logEntry->getRequestXml(),
                LogEntryInterface::FIELD_RESPONSE_XML => $logEntry->getResponseXml(),
                LogEntryInterface::FIELD_RESPONSE_TIME => $logEntry->getResponseTime(),
            ]
        );
        return $model;
    }
}
