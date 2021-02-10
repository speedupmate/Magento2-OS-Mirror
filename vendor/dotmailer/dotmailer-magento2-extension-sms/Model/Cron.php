<?php

namespace Dotdigitalgroup\Sms\Model;

use Dotdigitalgroup\Email\Logger\Logger;
use Dotdigitalgroup\Email\Model\Cron\JobChecker;
use Dotdigitalgroup\Sms\Model\SmsSenderManagerFactory;

class Cron
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SmsSenderManagerFactory
     */
    private $senderManager;

    /**
     * @var JobChecker
     */
    private $jobChecker;

    /**
     * Cron constructor.
     * @param Logger $logger
     * @param SmsSenderManagerFactory $senderManager
     * @param JobChecker $jobChecker
     */
    public function __construct(
        Logger $logger,
        SmsSenderManagerFactory $senderManager,
        JobChecker $jobChecker
    ) {
        $this->logger = $logger;
        $this->senderManager = $senderManager;
        $this->jobChecker = $jobChecker;
    }

    /**
     * @return void
     */
    public function sendSmsOrderMessages()
    {
        if ($this->jobChecker->hasAlreadyBeenRun('ddg_automation_sms_order_messages')) {
            $message = 'Skipping ddg_automation_sms_order_messages job run';
            $this->logger->info($message);
        }

        $this->senderManager->create()
            ->run();
    }
}
