<?php

namespace Dotdigitalgroup\Sms\Model\Queue;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Message\MessageBuilder;

class AfterSendProcessor
{
    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepository;

    /**
     * AfterSendProcessor constructor.
     * @param SmsOrderRepositoryInterface $smsOrderRepository
     */
    public function __construct(
        SmsOrderRepositoryInterface  $smsOrderRepository
    ) {
        $this->smsOrderRepository = $smsOrderRepository;
    }

    /**
     * Loop through the batched rows, assigning a message id from the response,
     * plus the message content from the cached $batchedContent.
     * The results will always be keyed according to the posted batch.
     *
     * @param SmsOrderInterface[] $itemsToProcess
     * @param array $results
     * @param array $messageBatch
     */
    public function process(array $itemsToProcess, array $results, array $messageBatch)
    {
        $batchRowIds = array_keys($itemsToProcess);

        foreach ($batchRowIds as $i => $rowId) {
            $item = $itemsToProcess[$rowId];

            $item->setMessageId($results[$i]->messageId)
                ->setStatus(OrderQueueManager::SMS_STATUS_IN_PROGRESS)
                ->setContent($messageBatch[$i]['body']);

            $this->smsOrderRepository->save($item);
        }
    }
}
