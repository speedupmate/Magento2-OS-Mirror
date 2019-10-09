<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Test\Unit\Model;

/**
 * Class ServiceClientTest
 */
class ServiceClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\GuzzleHttp\Client
     */
    private $client;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMock();

        $this->client = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatusCode', 'getBody', 'post', 'get', 'request', 'requestAsync'])
            ->getMock();

        $factoryMock = $this->getMockBuilder(\Magento\GoogleShoppingAds\Model\GuzzleClientFactory::class)
            ->disableOriginalConstructor()->getMock();

        $factoryMock->expects($this->once())->method('create')->willReturn($this->client);

        $uniqueIdManagerMock = $this->getMockBuilder(\Magento\GoogleShoppingAds\Model\UniqueIdManager::class)
            ->disableOriginalConstructor()->getMock();

        $this->serviceClient = new \Magento\GoogleShoppingAds\Model\ServiceClient(
            $this->scopeConfigMock,
            $factoryMock,
            $uniqueIdManagerMock
        );
    }

    public function testSendFeed()
    {
        $channelId = 1;
        $payload = 'payload';

        $this->scopeConfigMock->expects($this->any())->method('getValue')
            ->will($this->returnValueMap([
                [
                    \Magento\GoogleShoppingAds\Controller\Adminhtml\Index\MagentoGatewayCallback::PATH_MAGENTO_KEY,
                    'magento-key'
                ],
                ['sales_channels/sales_channel_integration/gateway_url', 'http://gateway.com'],
                [\Magento\GoogleShoppingAds\Model\ServiceClient::SERVICE_PATH, 'service/path']
            ]));

        $promise = new \GuzzleHttp\Promise\Promise();
        $this->client->expects($this->atLeastOnce())->method('requestAsync')->with(
            'POST',
            "channels/$channelId/feed/products",
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Expect' => '',
                    'magento-api-key' => null,
                    'x-magento-unique-id' => ''
                ],
                'body' => $payload
            ]
        )->willReturn($promise);

        $this->serviceClient->sendFeed($payload, $channelId);
    }
}
