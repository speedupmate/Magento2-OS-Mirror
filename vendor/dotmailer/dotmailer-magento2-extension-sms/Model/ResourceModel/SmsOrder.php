<?php

namespace Dotdigitalgroup\Sms\Model\ResourceModel;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Sms\Model\Queue\OrderQueueManager;
use Dotdigitalgroup\Sms\Setup\SchemaInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

class SmsOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Logger
     */
    private $logger;

    public function _construct()
    {
        $this->_init(SchemaInterface::EMAIL_SMS_ORDER_QUEUE_TABLE, 'id');
    }

    /**
     * @param Context $context
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Logger $logger
    ) {
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Update table rows with supplied status
     *
     * @param array $ids
     * @param string $status
     *
     * @return void
     */
    public function updateRowsWithStatus($ids, $status)
    {
        try {
            $this->getConnection()->update(
                $this->getTable(SchemaInterface::EMAIL_SMS_ORDER_QUEUE_TABLE),
                ['status' => $status],
                ['id IN(?)' => $ids]
            );
        } catch (\Exception $e) {
            $this->logger->debug((string) $e);
        }
    }

    /**
     * Set old pending rows to expired
     *
     * @param \DateTime $date
     * @return void
     */
    public function expirePendingRowsOlderThan($date)
    {
        $num = $this->getConnection()->update(
            $this->getTable(SchemaInterface::EMAIL_SMS_ORDER_QUEUE_TABLE),
            ['status' => OrderQueueManager::SMS_STATUS_EXPIRED],
            [
                'status = ?' => OrderQueueManager::SMS_STATUS_PENDING,
                'created_at <= ?' => $date->format('Y-m-d H:i:s')
            ]
        );

        if ($num) {
            $this->logger->info(
                'Expired ' . $num . ' pending SMS sends.'
            );
        }
    }
}
