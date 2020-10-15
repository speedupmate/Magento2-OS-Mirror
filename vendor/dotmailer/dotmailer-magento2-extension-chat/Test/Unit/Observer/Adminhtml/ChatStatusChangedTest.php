<?php

namespace Dotdigitalgroup\Email\Test\Unit\Observer\Adminhtml;

use Dotdigitalgroup\Email\Helper\Data;
use Dotdigitalgroup\Email\Model\Apiconnector\Client;
use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Chat\Observer\Adminhtml\ChatStatusChanged;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Website;
use Dotdigitalgroup\Email\Logger\Logger;
use PHPUnit\Framework\TestCase;

class ChatStatusChangedTest extends TestCase
{
    /**
     * @var Config
     */
    private $configMock;

    /**
     * @var Client
     */
    private $clientMock;

    /**
     * @var Context
     */
    private $contextMock;

    /**
     * @var Observer
     */
    private $observerMock;

    /**
     * @var ManagerInterface
     */
    private $managerInterfaceMock;

    /**
     * @var ChatStatusChanged
     */
    private $chatStatusChanged;

    /**
     * @var RequestInterface
     */
    private $requestInterfaceMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerMock;

    /**
     * @var Data
     */
    private $helperMock;

    /**
     * @var Website
     */
    private $websiteMock;

    /**
     * @var Logger
     */
    private $loggerMock;

    protected function setUp() :void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->clientMock = $this->createMock(Client::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->managerInterfaceMock = $this->createMock(ManagerInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPost'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helperMock = $this->createMock(Data::class);
        $this->websiteMock = $this->createMock(Website::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->chatStatusChanged = new ChatStatusChanged(
            $this->contextMock,
            $this->configMock,
            $this->managerInterfaceMock,
            $this->helperMock,
            $this->loggerMock
        );
    }

    private function getClientForWebsite()
    {
        $this->helperMock->expects($this->once())
            ->method('getWebsiteForSelectedScopeInAdmin')
            ->willReturn($this->websiteMock);

        $this->helperMock->expects($this->once())
            ->method('getWebsiteApiClient')
            ->with($this->websiteMock)
            ->willReturn($this->clientMock);
    }

    public function testIfChatIsSetToEnabledAndApiCredsAreValid()
    {
        $this->getClientForWebsite();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getPost')
            ->willReturn($this->getPostData(1));

        $this->configMock->expects($this->never())
            ->method('deleteChatApiCredentials');

        $this->clientMock->expects($this->once())
            ->method('setUpChatAccount')
            ->with([])
            ->willReturn($this->getResponse(true));

        $this->configMock->expects($this->once())
            ->method('saveChatApiSpaceId')
            ->willReturn($this->configMock);

        $this->configMock->expects($this->once())
            ->method('saveChatApiToken')
            ->willReturn(new class() {
                public function reinitialiseConfig()
                {
                }
            });

        $this->managerInterfaceMock->expects($this->never())
            ->method('addErrorMessage');

        $this->configMock->expects($this->never())
            ->method('setLiveChatStatus');

        $this->chatStatusChanged->execute($this->observerMock);
    }

    public function testIfChatIsSetToEnabledAndApiCredsAreInvalid()
    {
        $this->getClientForWebsite();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getPost')
            ->willReturn($this->getPostData(1));

        $this->clientMock->expects($this->once())
            ->method('setUpChatAccount')
            ->with([])
            ->willReturn($this->getResponse(false));

        $this->managerInterfaceMock->expects($this->once())
            ->method('addErrorMessage');

        $this->configMock->expects($this->once())
            ->method('setLiveChatStatus')
            ->with(false);

        $this->configMock->expects($this->exactly(1))
            ->method('deleteChatApiCredentials');

        $this->helperMock->expects($this->never())
            ->method('saveApiCredentials');

        $this->chatStatusChanged->execute($this->observerMock);
    }

    public function testIfChatIsSetToDisabled()
    {
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestInterfaceMock);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getPost')
            ->willReturn($this->getPostData(0));

        $this->configMock->expects($this->exactly(1))
            ->method('deleteChatApiCredentials');

        $this->managerInterfaceMock->expects($this->never())
            ->method('addErrorMessage');

        $this->configMock->expects($this->never())
            ->method('setLiveChatStatus');

        $this->helperMock->expects($this->never())
            ->method('saveApiCredentials');

        $this->chatStatusChanged->execute($this->observerMock);
    }

    /**
     * @param $isEnabled
     * @return array
     */
    private function getPostData($isEnabled)
    {
        return [
            'settings' => [
                'fields' => [
                    'enabled' => [
                        'value' => $isEnabled
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $isValid
     * @return \StdClass
     */
    private function getResponse($isValid)
    {
        $response = new \StdClass();
        if ($isValid) {
            $response->apiSpaceID = 'asdksajhdkjsasakds0shd';
            $response->token = 'eyzksjadkjahsdkjkj3243243kjsdf090df';
            return $response;
        }

        $response->message = 'Authorization denied for this request';
        return $response;
    }
}
