<?php

namespace Dotdigitalgroup\Sms\Model;

use Dotdigitalgroup\Email\Helper\Data;
use Dotdigitalgroup\Email\Model\Task\TaskRunInterface;
use Dotdigitalgroup\Sms\Model\Apiconnector\SmsClientFactory;
use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Dotdigitalgroup\Sms\Model\Message\MessageBuilder;
use Dotdigitalgroup\Sms\Model\Queue\AfterSendProcessor;
use Dotdigitalgroup\Sms\Model\Queue\OrderQueueManager;
use Dotdigitalgroup\Sms\Model\Queue\SenderProgressHandlerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class SmsSenderManager implements TaskRunInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SmsClientFactory
     */
    private $smsClientFactory;

    /**
     * @var TransactionalSms
     */
    private $transactionalSmsConfig;

    /**
     * @var AfterSendProcessor
     */
    private $afterSendProcessor;

    /**
     * @var OrderQueueManager
     */
    private $orderQueueManager;

    /**
     * @var SenderProgressHandlerFactory
     */
    private $senderProgressHandlerFactory;

    /**
     * @var MessageBuilder
     */
    private $messageBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * SmsSenderManager constructor.
     * @param Data $helper
     * @param SmsClientFactory $smsClientFactory
     * @param TransactionalSms $transactionalSmsConfig
     * @param AfterSendProcessor $afterSendProcessor
     * @param OrderQueueManager $orderQueueManager
     * @param SenderProgressHandlerFactory $senderProgressHandlerFactory
     * @param MessageBuilder $messageBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        SmsClientFactory $smsClientFactory,
        TransactionalSms $transactionalSmsConfig,
        AfterSendProcessor $afterSendProcessor,
        OrderQueueManager $orderQueueManager,
        SenderProgressHandlerFactory $senderProgressHandlerFactory,
        MessageBuilder $messageBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->smsClientFactory = $smsClientFactory;
        $this->transactionalSmsConfig = $transactionalSmsConfig;
        $this->afterSendProcessor = $afterSendProcessor;
        $this->orderQueueManager = $orderQueueManager;
        $this->senderProgressHandlerFactory = $senderProgressHandlerFactory;
        $this->messageBuilder = $messageBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     */
    public function run()
    {
        $activeApiUsers = $this->getAPIUsersForECEnabledWebsites();
        if (!$activeApiUsers) {
            return;
        }

        $this->orderQueueManager->expirePendingSends();

        foreach ($activeApiUsers as $apiUser) {
            $client = $this->smsClientFactory->create(
                $apiUser['firstWebsiteId']
            );
            if (!$client) {
                continue;
            }

            $this->senderProgressHandlerFactory->create(['data' => ['client' => $client]])
                ->updateSendsInProgress($apiUser['stores']);

            $queue = $this->orderQueueManager->getPendingQueue(
                $apiUser['stores']
            );
            if ($queue->getTotalCount() === 0) {
                continue;
            }

            $messageBatch = $this->messageBuilder->makeBatch($queue->getItems());
            $sendResults = $client->sendSmsBatch($messageBatch);

            if (is_array($sendResults)) {
                $this->afterSendProcessor->process(
                    $queue->getItems(),
                    $sendResults,
                    $messageBatch
                );
            }
        }
    }

    /**
     * Retrieve a list of active API users with the websites they are associated with.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAPIUsersForECEnabledWebsites()
    {
        $websites = $this->storeManager->getWebsites(true);
        $apiUsers = [];
        /** @var \Magento\Store\Model\Website $website */
        foreach ($websites as $website) {
            $websiteId = $website->getId();
            if ($this->helper->isEnabled($websiteId)) {
                $apiUser = $this->helper->getApiUsername($websiteId);
                foreach ($website->getStoreIds() as $storeId) {
                    if ($this->transactionalSmsConfig->isSmsEnabled($storeId)) {
                        if (!isset($apiUsers[$apiUser]['firstWebsiteId'])) {
                            $apiUsers[$apiUser]['firstWebsiteId'] = $websiteId;
                        }
                        $apiUsers[$apiUser]['stores'][] = $storeId;
                    }
                }
            }
        }
        return $apiUsers;
    }
}
