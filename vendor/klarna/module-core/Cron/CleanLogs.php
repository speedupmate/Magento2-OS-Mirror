<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Cron;

use Klarna\Core\Model\LogRepository;
use Klarna\Core\Model\ResourceModel\Log\Collection as LogCollection;
use Klarna\Core\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CleanLogs
{
    const SECONDSINDAY = 86400;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var LogCollection
     */
    private $logCollection;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var LogCollectionFactory
     */
    private $logCollectionFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $logLifetime = 'klarna/api/delete_request_logs_after';

    /**
     * @param ScopeConfigInterface  $config
     * @param StoreManagerInterface $storeManager
     * @param LogCollection         $logCollection
     * @param LogRepository         $logRepository
     * @param LogCollectionFactory  $logCollectionFactory
     * @param LoggerInterface       $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        LogCollection $logCollection,
        LogRepository $logRepository,
        LogCollectionFactory $logCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logCollection = $logCollection;
        $this->logRepository = $logRepository;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Clean expired logs (cron process).
     */
    public function execute(): void
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $logCollection = $this->getLogs($store);
            $logCollection->setPageSize(50);
            $lastPage = $logCollection->getSize() ? $logCollection->getLastPageNumber() : 0;

            for ($currentPage = $lastPage; $currentPage >= 1; $currentPage--) {
                $logCollection->setCurPage($currentPage);
                $this->deleteLogs($logCollection);
            }
        }
    }

    /**
     * Deletes all logs in collection
     *
     * @param LogCollection $logCollection
     */
    private function deleteLogs(LogCollection $logCollection): void
    {
        foreach ($logCollection as $log) {
            try {
                $this->logRepository->delete($log);
            } catch (LocalizedException $e) {
                $message = sprintf(
                    'Unable to delete log (ID: %s): %s',
                    $log->getId(),
                    (string)$e
                );
                $this->logger->error($message);
            }
        }

        $logCollection->clear();
    }

    /**
     * Gets logs.
     *
     * Log is considered expired if the created_at date
     * of the entry is greater than lifetime threshold
     *
     * @param StoreInterface $store
     * @return LogCollection
     */
    private function getLogs(StoreInterface $store): LogCollection
    {
        $lifetime = $this->config->getValue(
            $this->logLifetime,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $lifetime *= self::SECONDSINDAY;

        $logs = $this->logCollectionFactory->create();
        $logs->addFieldToFilter('store_id', $store->getId());
        $logs->addFieldToFilter('created_at', ['to' => date("Y-m-d", time() - $lifetime)]);

        return $logs;
    }
}
