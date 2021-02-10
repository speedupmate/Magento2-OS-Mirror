<?php

namespace Dotdigitalgroup\Sms\Model\Queue;

use Dotdigitalgroup\Sms\Model\Apiconnector\Client;
use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime;

class SenderProgressHandler extends DataObject
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepository;

    /**
     * @var OrderQueueManager
     */
    private $orderQueueManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * SenderProgressHandler constructor.
     *
     * @param SmsOrderRepositoryInterface $smsOrderRepository
     * @param OrderQueueManager $orderQueueManager
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        SmsOrderRepositoryInterface $smsOrderRepository,
        OrderQueueManager $orderQueueManager,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->smsOrderRepository = $smsOrderRepository;
        $this->orderQueueManager = $orderQueueManager;
        $this->dateTime = $dateTime;
        parent::__construct($data);
    }

    /**
     * @param array $storeIds
     */
    public function updateSendsInProgress(array $storeIds)
    {
        $inProgressQueue = $this->orderQueueManager->getInProgressQueue(
            $storeIds
        );
        if ($inProgressQueue->getTotalCount() === 0) {
            return;
        }

        $this->client = $this->getClient();

        /** @var SmsOrderInterface $item */
        foreach ($inProgressQueue->getItems() as $item) {
            $messageState = $this->client->getMessageByMessageId($item->getMessageId());

            if (!isset($messageState->messageId)) {
                $item->setStatus(OrderQueueManager::SMS_STATUS_UNKNOWN);
            } elseif (isset($messageState->status)) {
                if ($messageState->status === 'delivered') {
                    $item->setStatus(OrderQueueManager::SMS_STATUS_DELIVERED);
                    $item->setMessage($messageState->statusDetails->channelStatus->statusdescription);
                    $item->setSentAt($this->dateTime->formatDate($messageState->sentOn));
                } elseif ($messageState->status === 'failed') {
                    $item->setStatus(OrderQueueManager::SMS_STATUS_FAILED);
                    $item->setMessage($messageState->statusDetails->reason);
                }
            }

            $this->smsOrderRepository->save($item);
        }
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return $this->_getData('client');
    }
}
