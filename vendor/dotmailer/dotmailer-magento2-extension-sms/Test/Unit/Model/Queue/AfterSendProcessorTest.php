<?php

namespace Dotdigitalgroup\Sms\Test\Unit\Model\Queue;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Api\SmsOrderRepositoryInterface;
use Dotdigitalgroup\Sms\Model\Message\MessageBuilder;
use Dotdigitalgroup\Sms\Model\Queue\AfterSendProcessor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use PHPUnit\Framework\TestCase;

class AfterSendProcessorTest extends TestCase
{
    /**
     * @var SmsOrderRepositoryInterface
     */
    private $smsOrderRepositoryMock;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteriaMock;

    /**
     * @var SearchResultsInterface
     */
    private $searchResultsInterface;

    /**
     * @var AfterSendProcessor
     */
    private $afterSendProcessor;

    protected function setUp() :void
    {
        $this->smsOrderRepositoryMock = $this->createMock(SmsOrderRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $this->searchResultsInterfaceMock = $this->createMock(SearchResultsInterface::class);

        $this->afterSendProcessor = new AfterSendProcessor(
            $this->smsOrderRepositoryMock
        );
    }

    public function testRowsAreUpdatedWithNormalResults()
    {
        $results = $this->getExpectedResults();
        $messageBatch = $this->getMessageBatch();
        $items = $this->getSmsOrderMockArray(5, 4);

        $this->smsOrderRepositoryMock->expects($this->atLeast(4))
            ->method('save');

        $this->afterSendProcessor->process($items, $results, $messageBatch);
    }

    private function getSmsOrderMockArray($start, $multiple)
    {
        $smsOrderMocks = [];
        for ($i = $start; $i < ($start + $multiple); $i++) {
            $mock = $this->createMock(SmsOrderInterface::class);
            $mock->expects($this->any())
                ->method('setMessageId')
                ->willReturn($mock);
            $mock->expects($this->once())
                ->method('setStatus')
                ->willReturn($mock);
            $mock->expects($this->once())
                ->method('setContent')
                ->willReturn($mock);

            $smsOrderMocks[$i] = $mock;
        }

        return $smsOrderMocks;
    }

    private function getExpectedResults()
    {
        return [
            (object) [
                'index' => 0,
                'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc1'
            ],
            (object) [
                'index' => 1,
                'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc2'
            ],
            (object) [
                'index' => 2,
                'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc3'
            ],
            (object) [
                'index' => 3,
                'messageId' => '70266de2-ad1f-4acd-8588-456ad58acc4'
            ]
        ];
    }

    private function getMessageBatch()
    {
        return [
            [
                'body' => 'good message'
            ],
            [
                'body' => 'bad message'
            ],
            [
                'body' => 'my message'
            ],
            [
                'body' => 'chaz message'
            ],
        ];
    }
}
