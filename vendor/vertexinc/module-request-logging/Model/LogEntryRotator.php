<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntrySearchResultsInterface;
use Vertex\RequestLoggingApi\Api\LogEntryRepositoryInterface;
use Vertex\RequestLogging\Model\Config\Source\RotationAction;

/**
 * Move DB-based log entries to flat-file format.
 */
class LogEntryRotator
{
    /** @var Config */
    private $config;

    /** @var SearchCriteriaBuilderFactory */
    private $criteriaBuilderFactory;

    /** @var DateTime */
    private $dateTime;

    /** @var LogEntryExportFactory */
    private $exportFactory;

    /** @var LogEntryRepositoryInterface */
    private $logEntryRepository;

    /** @var TimezoneInterface */
    private $timezone;

    public function __construct(
        DateTime $dateTime,
        LogEntryRepositoryInterface $logEntryRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        LogEntryExportFactory $exportFactory,
        TimezoneInterface $timezone,
        Config $config
    ) {
        $this->dateTime = $dateTime;
        $this->logEntryRepository = $logEntryRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->exportFactory = $exportFactory;
        $this->timezone = $timezone;
        $this->config = $config;
    }

    /**
     * Rotate log entries older than the given lifetime value.
     *
     * @throws CouldNotDeleteException
     * @throws FileSystemException
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function rotate(int $lifetime): void
    {
        $dateTime = $this->timezone->scopeDate(null, time() - $lifetime, true);
        $clearBefore = $this->dateTime->formatDate($dateTime);

        /** @var SearchCriteriaBuilder $findCriteriaBuilder */
        $findCriteriaBuilder = $this->criteriaBuilderFactory->create();
        $findCriteriaBuilder->addFilter(LogEntryInterface::FIELD_REQUEST_DATE, $clearBefore, 'lteq');
        $findCriteriaBuilder->setPageSize($this->config->getCronAmountPerBatch());
        $findCriteria = $findCriteriaBuilder->create();

        while (($entries = $this->logEntryRepository->getList($findCriteria)) && $entries->getTotalCount()) {
            /** @var LogEntrySearchResultsInterface $entries */

            if ($this->config->getRotationAction() === RotationAction::TYPE_EXPORT) {
                $this->export($entries);
            }

            $entityIds = array_map(
                function (LogEntryInterface $logEntry) {
                    return $logEntry->getId();
                },
                $entries->getItems()
            );

            /** @var SearchCriteriaBuilder $deleteCriteriaBuilder */
            $deleteCriteriaBuilder = $this->criteriaBuilderFactory->create();
            $deleteCriteriaBuilder->addFilter(LogEntryInterface::FIELD_ID, $entityIds, 'in');
            $deleteCriteria = $deleteCriteriaBuilder->create();

            $this->logEntryRepository->deleteByCriteria($deleteCriteria);

            unset($entries, $entityIds, $deleteCriteria);
        }
    }

    /**
     * Export the given log entry set.
     *
     * @param LogEntrySearchResultsInterface $entries
     * @return void
     * @throws FileSystemException
     * @throws NotFoundException
     */
    private function export(LogEntrySearchResultsInterface $entries): void
    {
        /** @var LogEntryExport $export */
        $export = $this->exportFactory->create();

        $export->open();
        $export->writeHeader();

        /** @var LogEntryInterface $entry */
        foreach ($entries->getItems() as $entry) {
            $export->write($entry);
        }

        $export->close();
    }
}
