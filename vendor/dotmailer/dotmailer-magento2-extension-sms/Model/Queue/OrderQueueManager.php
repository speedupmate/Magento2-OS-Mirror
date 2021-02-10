<?php

namespace Dotdigitalgroup\Sms\Model\Queue;

use Dotdigitalgroup\Email\Model\DateIntervalFactory;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderQueueManager
{
    const SMS_STATUS_PENDING = 0;
    const SMS_STATUS_IN_PROGRESS = 1;
    const SMS_STATUS_DELIVERED = 2;
    const SMS_STATUS_FAILED = 3;
    const SMS_STATUS_EXPIRED = 4;
    const SMS_STATUS_UNKNOWN = 5;

    /**
     * @var DateIntervalFactory
     */
    private $dateIntervalFactory;

    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepository;

    /**
     * @var TransactionalSms
     */
    private $transactionalSmsConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * OrderQueueManager constructor.
     * @param DateIntervalFactory $dateIntervalFactory
     * @param SmsOrderRepositoryInterface $smsOrderRepository
     * @param TransactionalSms $transactionalSmsConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTimeFactory $dateTimeFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DateIntervalFactory $dateIntervalFactory,
        SmsOrderRepositoryInterface $smsOrderRepository,
        TransactionalSms $transactionalSmsConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTimeFactory $dateTimeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->dateIntervalFactory = $dateIntervalFactory;
        $this->smsOrderRepository = $smsOrderRepository;
        $this->transactionalSmsConfig = $transactionalSmsConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * The pending queue limits by batch size and filters out rows with no phone number.
     *
     * @param array $storeIds
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getPendingQueue(array $storeIds)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', self::SMS_STATUS_PENDING)
            ->addFilter('store_id', [$storeIds], 'in')
            ->addFilter('phone_number', null, 'neq')
            ->setPageSize($this->transactionalSmsConfig->getBatchSize());

        return $this->smsOrderRepository->getList($searchCriteria->create());
    }

    /**
     * @param array $storeIds
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getInProgressQueue(array $storeIds)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', self::SMS_STATUS_IN_PROGRESS)
            ->addFilter('store_id', [$storeIds], 'in');

        return $this->smsOrderRepository->getList($searchCriteria->create());
    }

    /**
     * @return void
     */
    public function expirePendingSends()
    {
        $now = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $oneDayAgo = $now->sub($this->dateIntervalFactory->create(['interval_spec' => 'PT24H']));

        $this->smsOrderRepository->expirePendingRowsOlderThan($oneDayAgo);
    }
}
