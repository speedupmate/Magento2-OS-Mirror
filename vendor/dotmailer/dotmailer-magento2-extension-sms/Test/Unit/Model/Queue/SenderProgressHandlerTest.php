<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Queue;

use Dotdigitalgroup\Sms\Model\Apiconnector\Client;
use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Queue\OrderQueueManager;
use Dotdigitalgroup\Sms\Model\Queue\SenderProgressHandler;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime;
use PHPStan\Testing\TestCase;

class SenderProgressHandlerTest extends TestCase
{
    /**
     * @var DataObject
     */
    private $dataObjectMock;

    /**
     * @var Client
     */
    private $clientMock;

    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepositoryMock;

    /**
     * @var OrderQueueManager
     */
    private $orderQueueManagerMock;

    /**
     * @var DateTime
     */
    private $dateTimeMock;

    /**
     * @var SenderProgressHandler
     */
    private $senderProgressHandler;

    /**
     * @var SearchResultsInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchResultsInterfaceMock;

    protected function setUp() :void
    {
        $this->smsOrderRepositoryMock = $this->createMock(SmsOrderRepositoryInterface::class);
        $this->orderQueueManagerMock = $this->createMock(OrderQueueManager::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->dataObjectMock = $this->createMock(DataObject::class);
        $this->searchResultsInterfaceMock = $this->createMock(SearchResultsInterface::class);

        $this->senderProgressHandler = new SenderProgressHandler(
            $this->smsOrderRepositoryMock,
            $this->orderQueueManagerMock,
            $this->dateTimeMock,
            ['client' => $this->createMock(Client::class)]
        );
    }

    public function testReturnIfNoPendingItems()
    {
        $storeIds = $this->getStoreIds();

        $this->orderQueueManagerMock->expects($this->once())
            ->method('getInProgressQueue')
            ->with($storeIds)
            ->willReturn($this->searchResultsInterfaceMock);

        $this->searchResultsInterfaceMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(0);

        $this->smsOrderRepositoryMock->expects($this->never())
            ->method('save');

        $this->senderProgressHandler->updateSendsInProgress($storeIds);
    }

    public function testHandleDeliveredMessages()
    {
        $storeIds = $this->getStoreIds();
        $messageStateMock = $this->getDeliveredMessageState();

        $this->orderQueueManagerMock->expects($this->once())
            ->method('getInProgressQueue')
            ->with($storeIds)
            ->willReturn($this->searchResultsInterfaceMock);

        $this->searchResultsInterfaceMock->expects($this->once())
            ->method('getItems')
            ->willReturn(
                $this->getDeliveredSmsMockArray(3)
            );

        $this->clientMock = $this->senderProgressHandler->getData('client');

        $this->clientMock->expects($this->atLeastOnce())
            ->method('getMessageByMessageId')
            ->willReturn($messageStateMock);

        $this->dateTimeMock->expects($this->atLeastOnce())
            ->method('formatDate')
            ->willReturn($messageStateMock->sentOn);

        $this->smsOrderRepositoryMock->expects($this->atLeastOnce())
            ->method('save');

        $this->senderProgressHandler->updateSendsInProgress($storeIds);
    }

    public function testHandleFailedMessage()
    {
        $storeIds = $this->getStoreIds();
        $messageStateMock = $this->getFailedMessageState();

        $this->orderQueueManagerMock->expects($this->once())
            ->method('getInProgressQueue')
            ->with($storeIds)
            ->willReturn($this->searchResultsInterfaceMock);

        $this->searchResultsInterfaceMock->expects($this->once())
            ->method('getItems')
            ->willReturn(
                $this->getFailedSmsMockArray(3)
            );

        $this->clientMock = $this->senderProgressHandler->getData('client');

        $this->clientMock->expects($this->atLeastOnce())
            ->method('getMessageByMessageId')
            ->willReturn($messageStateMock);

        $this->dateTimeMock->expects($this->never())
            ->method('formatDate');

        $this->smsOrderRepositoryMock->expects($this->atLeastOnce())
            ->method('save');

        $this->senderProgressHandler->updateSendsInProgress($storeIds);
    }

    public function testHandleMissingMessageId()
    {
        $storeIds = $this->getStoreIds();
        $messageStateMock = $this->getNoMessageState();

        $this->orderQueueManagerMock->expects($this->once())
            ->method('getInProgressQueue')
            ->with($storeIds)
            ->willReturn($this->searchResultsInterfaceMock);

        $this->searchResultsInterfaceMock->expects($this->once())
            ->method('getItems')
            ->willReturn(
                [$this->getErrorUnknownSmsMock()]
            );

        $this->clientMock = $this->senderProgressHandler->getData('client');

        $this->clientMock->expects($this->once())
            ->method('getMessageByMessageId')
            ->willReturn($messageStateMock);

        $this->smsOrderRepositoryMock->expects($this->atLeastOnce())
            ->method('save');

        $this->senderProgressHandler->updateSendsInProgress($storeIds);
    }

    private function getStoreIds()
    {
        return [1, 2, 3];
    }

    private function getDeliveredMessageState()
    {
        $state = [
            'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc1',
            'status' => 'delivered',
            'statusDetails' => [
                'channelStatus' => [
                    'statusdescription' => 'Messages delivered to handset'
                ]
            ],
            'sentOn' => '2020-10-06 16:00:00',
        ];

        return json_decode(json_encode($state));
    }

    private function getFailedMessageState()
    {
        $state = [
            'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc2',
            'status' => 'failed',
            'statusDetails' => [
                'reason' => 'Channel reported the message was undeliverable'
            ],
        ];

        return json_decode(json_encode($state));
    }

    private function getNoMessageState()
    {
        return (object) [
            'messageId' => null,
        ];
    }

    private function getDeliveredSmsMockArray($multiple)
    {
        $smsOrderMocks = [];
        for ($i = 0; $i < $multiple; $i++) {
            $mock = $this->createMock(SmsOrderInterface::class);
            $mock->expects($this->any())
                ->method('getMessageId')
                ->willReturn($mock);
            $mock->expects($this->once())
                ->method('setStatus')
                ->willReturn($mock);
            $mock->expects($this->any())
                ->method('setMessage')
                ->willReturn($mock);
            $mock->expects($this->once())
                ->method('setSentAt')
                ->willReturn($mock);

            $smsOrderMocks[] = $mock;
        }

        return $smsOrderMocks;
    }

    private function getFailedSmsMockArray($multiple)
    {
        $smsOrderMocks = [];
        for ($i = 0; $i < $multiple; $i++) {
            $mock = $this->createMock(SmsOrderInterface::class);
            $mock->expects($this->any())
                ->method('getMessageId')
                ->willReturn($mock);
            $mock->expects($this->once())
                ->method('setStatus')
                ->willReturn($mock);
            $mock->expects($this->any())
                ->method('setMessage')
                ->willReturn($mock);

            $smsOrderMocks[] = $mock;
        }

        return $smsOrderMocks;
    }

    private function getErrorUnknownSmsMock()
    {
        $mock = $this->createMock(SmsOrderInterface::class);
        $mock->expects($this->once())
            ->method('setStatus')
            ->willReturn($mock);
        return $mock;
    }
}
