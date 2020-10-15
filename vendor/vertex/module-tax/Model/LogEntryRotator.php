<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Stdlib\DateTime;
use Vertex\Tax\Api\Data\LogEntryInterface;
use Vertex\Tax\Api\Data\LogEntrySearchResultsInterface;
use Vertex\Tax\Api\LogEntryRepositoryInterface;
use Vertex\Tax\Model\Config\Source\RotationAction;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rotate(int $lifetime) :void
    {
        $dateTime = $this->timezone->scopeDate(null, time() - $lifetime, true);
        $clearAfter = $this->dateTime->formatDate($dateTime);

        /** @var SearchCriteriaBuilder $findCriteriaBuilder */
        $findCriteriaBuilder = $this->criteriaBuilderFactory->create();
        $findCriteriaBuilder->addFilter(LogEntryInterface::FIELD_REQUEST_DATE, $clearAfter, 'lteq');
        $findCriteriaBuilder->setPageSize(100);
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
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function export(LogEntrySearchResultsInterface $entries)
    {
        /** @var LogEntryExport $export */
        $export = $this->exportFactory->create();

        $export->open();
        $export->writeHeader();

        /** @var \Vertex\Tax\Api\Data\LogEntryInterface $entry */
        foreach ($entries->getItems() as $entry) {
            $export->write($entry);
        }

        $export->close();
    }
}
