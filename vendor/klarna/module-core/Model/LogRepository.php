<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Core\Model;

use Klarna\Core\Api\Data\LogInterface;
use Klarna\Core\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Klarna\Core\Api\LogRepositoryInterface;
use Klarna\Core\Model\ResourceModel\Log;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Klarna\Core\Model\ResourceModel\Log\Collection;

/**
 * Repository class for the logs
 */
class LogRepository implements LogRepositoryInterface
{
    /**
     * @var Log
     */
    private $resourceModel;
    /**
     * @var LogFactory
     */
    private $logFactory;
    /**
     * @var LogCollectionFactory
     */
    private $logCollectionFactory;
    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param Log                              $resourceModel
     * @param LogFactory                       $logFactory
     * @param LogCollectionFactory             $logCollectionFactory
     * @param SearchResultsInterfaceFactory    $searchResultsFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Log $resourceModel,
        LogFactory $logFactory,
        LogCollectionFactory $logCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel        = $resourceModel;
        $this->logFactory           = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(LogInterface $log): LogInterface
    {
        try {
            $this->resourceModel->save($log);
            return $log;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(LogInterface $log): LogInterface
    {
        try {
            $this->resourceModel->delete($log);
            return $log;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteById(string $id): LogInterface
    {
        $log = $this->getById($id);
        return $this->delete($log);
    }

    /**
     * @inheritdoc
     */
    public function getById(string $logId): LogInterface
    {
        $log = $this->logFactory->create();
        $this->resourceModel->load($log, $logId);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('The log with the "%1" ID doesn\'t exist.', $logId));
        }
        return $log;
    }

    /**
     * @inheritdoc
     */
    public function getListGroupedByKlarnaId(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->getCollection($searchCriteria);
        $collection->getSelect()
            ->group('klarna_id');

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->getCollection($searchCriteria);

        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Getting back the collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return Collection
     */
    private function getCollection(SearchCriteriaInterface $searchCriteria): Collection
    {
        $collection = $this->logCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        return $collection;
    }
}
