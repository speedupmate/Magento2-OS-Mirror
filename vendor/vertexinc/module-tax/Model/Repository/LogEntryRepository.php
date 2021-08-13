<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface as ApiLogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterfaceFactory;
use Vertex\RequestLoggingApi\Api\LogEntryRepositoryInterface as ApiRepository;
use Vertex\Tax\Api\Data\LogEntryInterface;
use Vertex\Tax\Api\Data\LogEntrySearchResultsInterface;
use Vertex\Tax\Api\Data\LogEntrySearchResultsInterfaceFactory;
use Vertex\Tax\Api\LogEntryRepositoryInterface;
use Vertex\Tax\Model\Data\LogEntry;
use Vertex\Tax\Model\Data\LogEntryFactory;

/**
 * Repository of Log Entries
 */
class LogEntryRepository implements LogEntryRepositoryInterface
{
    /** @var LogEntryInterfaceFactory */
    private $apiInterfaceFactory;

    /** @var LogEntryFactory */
    private $logEntryFactory;

    /** @var ApiRepository */
    private $proxiedRepository;

    /** @var LogEntrySearchResultsInterfaceFactory */
    private $searchResultsFactory;

    public function __construct(
        LogEntryInterfaceFactory $apiInterfaceFactory,
        LogEntrySearchResultsInterfaceFactory $searchResultsFactory,
        ApiRepository $proxiedRepository,
        LogEntryFactory $logEntryFactory
    ) {
        $this->apiInterfaceFactory = $apiInterfaceFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->proxiedRepository = $proxiedRepository;
        $this->logEntryFactory = $logEntryFactory;
    }

    public function delete(LogEntryInterface $logEntry)
    {
        return $this->deleteById($logEntry->getId());
    }

    public function deleteByCriteria(SearchCriteriaInterface $searchCriteria)
    {
        return $this->proxiedRepository->deleteByCriteria($searchCriteria);
    }

    public function deleteById($logEntryId)
    {
        return $this->proxiedRepository->deleteById((int)$logEntryId);
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var LogEntrySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        $proxiedResult = $this->proxiedRepository->getList($searchCriteria);

        $searchResults->setTotalCount($proxiedResult->getTotalCount());

        $logEntries = [];
        foreach ($proxiedResult->getItems() as $logEntry) {
            $logEntries[] = $this->convertToDeprecatedInterface($logEntry);
        }
        $searchResults->setItems($logEntries);
        return $searchResults;
    }

    public function save(LogEntryInterface $logEntry)
    {
        $model = $this->convertToApiInterface($logEntry);
        $this->proxiedRepository->save($model);
        return $this;
    }

    /**
     * Convert a LogEntryInterface into a LogEntry model
     *
     * @param LogEntryInterface $logEntry
     * @return ApiLogEntryInterface
     */
    private function convertToApiInterface(LogEntryInterface $logEntry): ApiLogEntryInterface
    {
        /** @var ApiLogEntryInterface $model */
        $model = $this->apiInterfaceFactory->create();
        if ($logEntry->getId() !== null) {
            $model->setId((int)$logEntry->getId());
        }
        $model->setType($logEntry->getType());
        $model->setOrderId((int)$logEntry->getOrderId());
        $model->setTotalTax((float)$logEntry->getTotalTax());
        $model->setTaxAreaId((int)$logEntry->getTaxAreaId());
        $model->setSubTotal((float)$logEntry->getSubTotal());
        $model->setTotal((float)$logEntry->getTotal());
        $model->setLookupResult($logEntry->getLookupResult());
        $model->setDate($logEntry->getDate());
        $model->setRequestXml($logEntry->getRequestXml());
        $model->setResponseXml($logEntry->getResponseTime());
        $model->setResponseTime((int)$logEntry->getResponseTime());
        return $model;
    }

    private function convertToDeprecatedInterface(ApiLogEntryInterface $logEntry): LogEntry
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
                LogEntryInterface::FIELD_REQUEST_DATE => $logEntry->getDate(),
                LogEntryInterface::FIELD_REQUEST_XML => $logEntry->getRequestXml(),
                LogEntryInterface::FIELD_RESPONSE_XML => $logEntry->getResponseXml(),
                LogEntryInterface::FIELD_RESPONSE_TIME => $logEntry->getResponseTime(),
            ]
        );
        return $model;
    }
}
