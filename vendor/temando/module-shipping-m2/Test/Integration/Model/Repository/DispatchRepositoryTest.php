<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Dispatch;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Model\DispatchInterface;
use Temando\Shipping\Model\ResourceModel\Repository\DispatchRepositoryInterface;
use Temando\Shipping\Model\Config\ModuleConfig;
use Temando\Shipping\Rest\AuthenticationInterface;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;
use Temando\Shipping\Test\Integration\Provider\RestResponseProvider;
use Temando\Shipping\Webservice\HttpClientInterfaceFactory;

/**
 * Temando Dispatch Repository Test
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture createApiToken
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class DispatchRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Delegate provisioning of test data to separate class
     * @return string[]
     */
    public function getCompletionResponseDataProvider()
    {
        return RestResponseProvider::getCompletionResponseDataProvider();
    }

    /**
     * delegate fixtures creation to separate class.
     */
    public static function createApiToken()
    {
        ApiTokenFixture::createValidToken();
    }

    /**
     * delegate fixtures rollback to separate class.
     */
    public static function createApiTokenRollback()
    {
        ApiTokenFixture::rollbackToken();
    }

    /**
     * Assert that the dispatch repository throws a LocalizedException when api request fails.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function apiItemAccessFails()
    {
        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setNextRequestWillFail(true);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var DispatchRepositoryInterface $dispatchRepository */
        $dispatchRepository = Bootstrap::getObjectManager()->get(DispatchRepositoryInterface::class);
        $dispatchRepository->getById('foo');
    }

    /**
     * Assert that the dispatch repository throws a LocalizedException when api returns error response.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function apiItemReturnsError()
    {
        $responseBody = '{"message":"Internal server error"}';
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var DispatchRepositoryInterface $dispatchRepository */
        $dispatchRepository = Bootstrap::getObjectManager()->get(DispatchRepositoryInterface::class);
        $dispatchRepository->getById('foo');
    }

    /**
     * Assert that the dispatch repository throws a NoSuchEntityException when api returns 404 response.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apiItemReturnsEmptyResult()
    {
        $responseBody = '{"errors":[{"status":404,"title":"Completion \'foo\' not found","code":"InternalError"}]}';
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var DispatchRepositoryInterface $dispatchRepository */
        $dispatchRepository = Bootstrap::getObjectManager()->get(DispatchRepositoryInterface::class);
        $dispatchRepository->getById('foo');
    }

    /**
     * Assert that a non-empty api response is properly passed through the repository.
     *
     * @test
     * @dataProvider getCompletionResponseDataProvider
     *
     * @magentoConfigFixture default/carriers/temando/session_endpoint https://auth.temando.io/v1/
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @param string $responseBody
     */
    public function getById($responseBody)
    {
        $completionId = '444cc444-ffff-dddd-eeee-bbbaaddd2000';

        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_200);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(\Temando\Shipping\Webservice\HttpClient::class, [
            'client' => $zendClient,
        ]);

        $clientFactoryMock = $this->getMockBuilder(HttpClientInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $clientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($httpClient);
        Bootstrap::getObjectManager()->addSharedInstance($clientFactoryMock, HttpClientInterfaceFactory::class);

        /** @var DispatchRepositoryInterface $dispatchRepository */
        $dispatchRepository = Bootstrap::getObjectManager()->get(DispatchRepositoryInterface::class);
        $dispatch = $dispatchRepository->getById($completionId);
        $this->assertInstanceOf(DispatchInterface::class, $dispatch);
        $this->assertEquals($completionId, $dispatch->getDispatchId());
        $this->assertEquals('processed', $dispatch->getStatus());
    }
}
