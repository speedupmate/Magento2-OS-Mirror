<?php

namespace Dotdigitalgroup\Sms\Plugin;

use Dotdigitalgroup\Email\Model\Cron\Cleaner;
use Dotdigitalgroup\Sms\Setup\SchemaInterface;

class CronCleanerPlugin
{
    /**
     * @var array
     */
    private $tables = [
        'sms_order_queue' => SchemaInterface::EMAIL_SMS_ORDER_QUEUE_TABLE
    ];

    /**
     * @param Cleaner $cleaner
     * @param array $additionalSyncs
     * @return array
     */
    public function beforeGetTablesForCleanUp(Cleaner $cleaner, array $additionalTables = [])
    {
        return [
            '$additionalTables' => $this->tables,
        ];
    }
}
