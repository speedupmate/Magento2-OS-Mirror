<?php

namespace Dotdigitalgroup\Sms\Plugin;

use Dotdigitalgroup\Sms\Model\SmsSenderManagerFactory;
use Dotdigitalgroup\Email\Console\Command\Provider\TaskProvider;

class TaskProviderPlugin
{
    /**
     * @var SmsSenderManagerFactory
     */
    private $smsSenderManagerFactory;

    /**
     * @param SmsSenderManagerFactory $smsSenderManagerFactory
     */
    public function __construct(
        SmsSenderManagerFactory $smsSenderManagerFactory
    ) {
        $this->smsSenderManagerFactory = $smsSenderManagerFactory;
    }

    /**
     * @param TaskProvider $taskProvider
     * @param array $additionalSyncs
     * @return array
     */
    public function beforeGetAvailableTasks(TaskProvider $taskProvider, array $additionalSyncs = [])
    {
        return [
            'additionalSyncs' => get_object_vars($this),
        ];
    }
}
