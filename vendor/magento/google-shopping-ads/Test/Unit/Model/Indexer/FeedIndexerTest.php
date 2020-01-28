<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Test\Unit\Model\Indexer;

/**
 * Class FeedIndexerTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FeedIndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\ProductRetriever
     */
    private $productRetrieverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetrieverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever
     */
    private $removeRetrieverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\Indexer\FeedSender
     */
    private $feedSenderMock;

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer
     */
    private $attributeIndexer;

    public function setUp()
    {
        $this->productRetrieverMock = $this->getMockBuilder(\Magento\GoogleShoppingAds\Model\ProductRetriever::class)
            ->disableOriginalConstructor()->getMock();

        $this->serviceDataRetrieverMock = $this
            ->getMockBuilder(\Magento\GoogleShoppingAds\Model\ServiceDataRetriever::class)
            ->disableOriginalConstructor()->getMock();

        $this->serviceDataRetrieverMock->expects($this->once())
            ->method('getWebsiteConfigs')
            ->will($this->returnValue('{"1":{"channelId":"7f6e8730-fa39-11e8-912c-fd44f285c6fd",'
                . '"channelAttributes":{"webSiteId":"1"}}}'));
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->removeRetrieverMock = $this
            ->getMockBuilder(\Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever::class)
            ->disableOriginalConstructor()->getMock();

        $this->feedSenderMock = $this->getMockBuilder(\Magento\GoogleShoppingAds\Model\Indexer\FeedSender::class)
            ->disableOriginalConstructor()->getMock();

        $this->attributeIndexer = new \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer(
            [$this->productRetrieverMock],
            $this->removeRetrieverMock,
            $this->serviceDataRetrieverMock,
            $this->loggerMock,
            $this->feedSenderMock
        );
    }

    private function initMocks($withError = false)
    {
        $product1 = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product1->expects($this->any())->method('getId')->willReturn(1);

        $product2 = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product2->expects($this->any())->method('getId')->willReturn(2);

        $this->removeRetrieverMock->expects($this->atLeast(1))
            ->method('getRemovedIds')
            ->willReturn([]);

        $this->productRetrieverMock->expects($this->atLeast(1))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls(
                [$product1],
                [$product2],
                [$product2]
            );

        if (!$withError) {
            $promise = new \GuzzleHttp\Promise\FulfilledPromise(new \GuzzleHttp\Psr7\Response());
            $this->feedSenderMock->expects($this->atLeast(1))
                ->method('sendFeed')
                ->willReturn($promise);
        } else {
            $promise = new \GuzzleHttp\Promise\Promise();
            $this->feedSenderMock->expects($this->atLeast(1))
                ->method('sendFeed')
                ->willReturn($promise);
        }
    }

    public function testExecuteFull()
    {
        $this->initMocks();
        $this->attributeIndexer->executeFull();
    }

    public function testExecuteFullException()
    {
        $this->initMocks(true);
        $this->expectException(\GuzzleHttp\Promise\RejectionException::class);
        $this->attributeIndexer->executeFull();
    }

    public function testExecuteList()
    {
        $this->initMocks();
        $this->attributeIndexer->executeList([1]);
    }

    public function testExecuteRow()
    {
        $this->initMocks();
        $this->attributeIndexer->executeRow(1);
    }

    public function testExecute()
    {
        $this->initMocks();
        $this->attributeIndexer->execute([1]);
    }
}
