<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Observer;

use Magento\Framework\Message\Manager as MessageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Model\Shipping\Carrier;
use Temando\Shipping\Rest\AuthAdapter as RestAdapter;
use Temando\Shipping\Rest\Authentication;
use Temando\Shipping\Rest\RestClient;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;
use Temando\Shipping\Test\Integration\Provider\RestResponseProvider;
use Temando\Shipping\Webservice\Exception\HttpResponseException;
use Temando\Shipping\Webservice\HttpClient;
use Temando\Shipping\Webservice\HttpClientInterfaceFactory;

/**
 * AdminLoginObserverTest
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture createExpiredApiToken
 */
class AdminLoginObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Invoker\InvokerDefault
     */
    private $invoker;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var HttpClientInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactory;

    /**
     * @var HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * Init object manager
     */
    public function setUp()
    {
        parent::setUp();

        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $this->invoker = $objectManager->get(\Magento\Framework\Event\InvokerInterface::class);
        $this->observer = $objectManager->get(\Magento\Framework\Event\Observer::class);

        $carrierMock = $this->getMockBuilder(Carrier::class)
                            ->setMethods(['getConfigFlag'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $carrierMock->expects($this->once())
                    ->method('getConfigFlag')
                    ->with('active')
                    ->willReturn(($this->getName(false) !== 'carrierIsNotActive'));
        $objectManager->addSharedInstance($carrierMock, Carrier::class);

        // prepare the http connection to be mocked
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
                                 ->setMethods(['send'])
                                 ->setConstructorArgs(['client' => new \Zend\Http\Client()])
                                 ->getMock();

        $this->httpClientFactory = $this->getMockBuilder(HttpClientInterfaceFactory::class)
                                        ->setMethods(['create'])
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->messageManager = $this->getMockBuilder(MessageManager::class)
                                     ->setMethods(['addWarningMessage', 'addExceptionMessage'])
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $objectManager->addSharedInstance($this->messageManager, MessageManager::class);

        $restClient = $objectManager->create(RestClient::class, [
            'httpClientFactory' => $this->httpClientFactory,
        ]);
        $objectManager->addSharedInstance($restClient, RestClient::class);
    }

    protected function tearDown()
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->removeSharedInstance(AdminLoginObserver::class);
        $objectManager->removeSharedInstance(Authentication::class);
        $objectManager->removeSharedInstance(RestAdapter::class);

        parent::tearDown();
    }

    /**
     * Delegate provisioning of test data to separate class
     * @return string[]
     */
    public function startSessionSuccessResponseDataProvider()
    {
        return RestResponseProvider::startSessionResponseDataProvider();
    }

    /**
     * Delegate provisioning of test data to separate class
     * @return string[]
     */
    public function startSessionFailureResponseDataProvider()
    {
        return RestResponseProvider::startSessionValidationErrorResponseDataProvider();
    }

    /**
     * delegate fixtures creation to separate class.
     */
    public static function createExpiredApiToken()
    {
        ApiTokenFixture::createExpiredToken();
    }

    /**
     * delegate fixtures rollback to separate class.
     */
    public static function createExpiredApiTokenRollback()
    {
        ApiTokenFixture::rollbackToken();
    }

    /**
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/account_id accountId
     * @magentoConfigFixture default/carriers/temando/bearer_token bearerToken
     */
    public function carrierIsNotActive()
    {
        $this->httpClient
            ->expects($this->never())
            ->method('send');
        $this->httpClientFactory
            ->expects($this->never())
            ->method('create');

        $this->messageManager
            ->expects($this->never())
            ->method('addWarningMessage');
        $this->messageManager
            ->expects($this->never())
            ->method('addExceptionMessage');

        $config = [
            'instance' => AdminLoginObserver::class,
            'name' => 'temando_admin_login',
        ];
        $this->invoker->dispatch($config, $this->observer);
    }

    /**
     * @test
     */
    public function carrierIsActiveButCredentialsAreMissing()
    {
        $this->httpClient
            ->expects($this->never())
            ->method('send');
        $this->httpClientFactory
            ->expects($this->never())
            ->method('create');

        $this->messageManager
            ->expects($this->once())
            ->method('addWarningMessage');
        $this->messageManager
            ->expects($this->never())
            ->method('addExceptionMessage');

        $config = [
            'instance' => AdminLoginObserver::class,
            'name' => 'temando_admin_login',
        ];
        $this->invoker->dispatch($config, $this->observer);
    }

    /**
     * @test
     * @dataProvider startSessionSuccessResponseDataProvider
     *
     * @magentoConfigFixture default/carriers/temando/bearer_token foo
     * @magentoConfigFixture default/carriers/temando/account_id bar
     *
     * @param string $jsonResponse
     */
    public function sessionRefreshSuccess($jsonResponse)
    {
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->willReturn($jsonResponse);
        $this->httpClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClient);

        $this->messageManager
            ->expects($this->never())
            ->method('addWarningMessage');
        $this->messageManager
            ->expects($this->never())
            ->method('addExceptionMessage');

        $config = [
            'instance' => AdminLoginObserver::class,
            'name' => 'temando_admin_login',
        ];
        $this->invoker->dispatch($config, $this->observer);
    }

    /**
     * @test
     * @dataProvider startSessionFailureResponseDataProvider
     *
     * @magentoConfigFixture default/carriers/temando/bearer_token foo
     * @magentoConfigFixture default/carriers/temando/account_id bar
     *
     * @param string $jsonResponse
     */
    public function sessionRefreshFailure($jsonResponse)
    {
        $httpException = new HttpResponseException($jsonResponse);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->willThrowException($httpException);
        $this->httpClientFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->httpClient);

        $this->messageManager
            ->expects($this->never())
            ->method('addWarningMessage');
        $this->messageManager
            ->expects($this->once())
            ->method('addExceptionMessage');

        $config = [
            'instance' => AdminLoginObserver::class,
            'name' => 'temando_admin_login',
        ];
        $this->invoker->dispatch($config, $this->observer);
    }
}
