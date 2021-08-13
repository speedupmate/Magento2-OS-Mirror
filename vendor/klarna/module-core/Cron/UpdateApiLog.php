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

namespace Klarna\Core\Cron;

use Klarna\Core\Model\LogRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

class UpdateApiLog
{
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param LogRepository         $logRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @codeCoverageIgnore
     */
    public function __construct(LogRepository $logRepository, SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->logRepository = $logRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Updating the increment_id field in the Klarna logs table
     *
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $incrementIds = $this->getIncrementIds();
        $this->updateIncrementId($incrementIds);
    }

    /**
     * Getting back the increment ids
     *
     * @return array
     */
    private function getIncrementIds(): array
    {
        $this->searchCriteriaBuilder->addFilter('increment_id', null, 'neq');
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $logs = $this->logRepository->getListGroupedByKlarnaId($searchCriteria);

        $result = [];
        foreach ($logs->getItems() as $item) {
            $result[$item->getKlarnaId()] = $item->getIncrementId();
        }

        return $result;
    }

    /**
     * Updating the increment id column for the rows which does not have a increment id value
     *
     * @param array $incrementIds
     * @throws LocalizedException
     */
    private function updateIncrementId(array $incrementIds)
    {
        $this->searchCriteriaBuilder->addFilter('increment_id', null, 'null');
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $logs = $this->logRepository->getList($searchCriteria);
        foreach ($logs->getItems() as $item) {
            if (!isset($incrementIds[$item->getKlarnaId()])) {
                continue;
            }
            $item->setIncrementId($incrementIds[$item->getKlarnaId()]);
            $this->logRepository->save($item);
        }
    }
}
