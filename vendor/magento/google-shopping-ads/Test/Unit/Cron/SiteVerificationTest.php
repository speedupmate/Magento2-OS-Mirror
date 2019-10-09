<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Test\Unit\Cron;

/**
 * Class SiteVerificationTest
 */
class SiteVerificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClientMock;

    /**
     * @var \Magento\GoogleShoppingAds\Cron\SiteVerification
     */
    private $siteVerification;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\TypeListInterface
     */
    private $typeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetrieverMock;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->serviceClientMock = $this->getMockBuilder(\Magento\GoogleShoppingAds\Model\ServiceClient::class)
            ->disableOriginalConstructor()->getMock();

        $this->typeListMock = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->writerMock = $this->getMockBuilder(\Magento\Framework\App\Config\Storage\WriterInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->serviceDataRetrieverMock = $this
            ->getMockBuilder(\Magento\GoogleShoppingAds\Model\ServiceDataRetriever::class)
            ->disableOriginalConstructor()->getMock();

        $this->siteVerification = new \Magento\GoogleShoppingAds\Cron\SiteVerification(
            $this->scopeConfigMock,
            $this->serviceClientMock,
            $this->typeListMock,
            $this->writerMock,
            $this->serviceDataRetrieverMock
        );
    }

    public function testExecuteVerification()
    {
        $this->serviceDataRetrieverMock->expects($this->once())
            ->method('getWebsiteConfigs')
            ->will($this->returnValue('{"1":{"channelId":"7f6e8730-fa39-11e8-912c-fd44f285c6fd",'
                . '"channelAttributes":{"webSiteId":"1"},"websiteClaimed": false}}'));
        $this->serviceClientMock->expects($this->once())
            ->method('requestVerification')
            ->willReturn(['body' => '{"result":"true"}']);
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->willReturn('{"1":{"code":"some-code","isVerified":false,"attempts":0}}');
        $this->writerMock->expects($this->once())->method('save');
        $this->siteVerification->execute();
    }

    public function testExecuteRetrieveCode()
    {
        $this->serviceDataRetrieverMock->expects($this->once())
            ->method('getWebsiteConfigs')
            ->will($this->returnValue('{"1":{"channelId":"7f6e8730-fa39-11e8-912c-fd44f285c6fd",'
                . '"channelAttributes":{"webSiteId":"1"},"websiteClaimed": false}}'));
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->willReturn(null);
        $this->serviceClientMock->expects($this->once())
            ->method('getVerificationCode')
            ->willReturn(['body' => '{"verification-code":"some-code"}']);
        $this->writerMock->expects($this->once())->method('save');
        $this->siteVerification->execute();
    }
}
