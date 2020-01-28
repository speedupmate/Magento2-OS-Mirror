<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\Dispatch;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Model\DispatchInterface;
use Temando\Shipping\Model\DispatchProvider;
use Temando\Shipping\Model\ResourceModel\Dispatch\DispatchRepository;
use Temando\Shipping\Model\ResourceModel\Repository\DispatchRepositoryInterface;
use Temando\Shipping\Rest\Adapter;
use Temando\Shipping\Rest\RestClient;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;
use Temando\Shipping\Test\Integration\Provider\RestResponseProvider;
use Temando\Shipping\Webservice\HttpClientInterface;
use Temando\Shipping\Webservice\HttpClientInterfaceFactory;

/**
 * Temando View Dispatch Page Test
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixture createApiToken
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown()
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->removeSharedInstance(RestClient::class);
        $objectManager->removeSharedInstance(DispatchProvider::class);
        $objectManager->removeSharedInstance(DispatchRepository::class);
        $objectManager->removeSharedInstance(Adapter::class);

        parent::tearDown();
    }

    /**
     * Delegate provisioning of test data to separate class
     * @return string[]
     */
    public function getCompletionDataProvider()
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
     * Assert dispatch listing url is being generated.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     */
    public function getDispatchListingPageUrl()
    {
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var View $block */
        $block = $layout->createBlock(View::class);

        $this->assertContains('dispatch/index', $block->getDispatchesPageUrl());
    }

    /**
     * Assert exception is caught if repository cannot load dispatch.
     *
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     */
    public function dispatchCannotBeLoaded()
    {
        // prepare dispatch view request
        $request = Bootstrap::getObjectManager()->get(\Magento\TestFramework\Request::class);
        $request->setParam('dispatch_id', 'f00');

        // prepare api response (dispatch not found)
        $body = '{"errors":[{"status":"404","title":"Completion with id \'f00\' not found.","code":"NotFoundError"}]}';
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        $testResponse->setContent($body);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(HttpClientInterface::class, [
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

        // obtain dispatch through block
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var View $block */
        $block = $layout->createBlock(View::class);

        $this->assertNull($block->getDispatch());
    }

    /**
     * @test
     * @dataProvider getCompletionDataProvider
     *
     * @magentoConfigFixture default/carriers/temando/sovereign_endpoint https://foo.temando.io/v1/
     *
     * @param string $responseBody
     */
    public function dispatchIsLoaded($responseBody)
    {
        $dispatchId = '444cc444-ffff-dddd-eeee-bbbaaddd2000';

        // prepare dispatch view request
        $request = Bootstrap::getObjectManager()->get(\Magento\TestFramework\Request::class);
        $request->setParam('dispatch_id', $dispatchId);

        // prepare api response (dispatch not found)
        $testResponse = new \Zend\Http\Response();
        $testResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_200);
        $testResponse->setContent($responseBody);

        $testAdapter = new \Zend\Http\Client\Adapter\Test();
        $testAdapter->setResponse($testResponse);

        $zendClient = new \Zend\Http\Client();
        $zendClient->setAdapter($testAdapter);

        $httpClient = Bootstrap::getObjectManager()->create(HttpClientInterface::class, [
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
        $dispatch = $dispatchRepository->getById($dispatchId);

        /** @var DispatchProvider $dispatchProvider */
        $dispatchProvider = Bootstrap::getObjectManager()->get(DispatchProvider::class);
        $dispatchProvider->setDispatch($dispatch);

        // obtain dispatch through block
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var View $block */
        $block = $layout->createBlock(View::class);

        /** @var DispatchInterface $completion */
        $completion = $block->getDispatch();
        $this->assertInstanceOf(DispatchInterface::class, $completion);
        $this->assertEquals($dispatchId, $completion->getDispatchId());
    }

    /**
     * @test
     *
     * @magentoConfigFixture default/general/locale/timezone Europe/London
     */
    public function getLocalizedDate()
    {
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var View $block */
        $block = $layout->createBlock(View::class);

        $dateTime = $block->getDate('2017-01-01T00:00:01Z');
        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertEquals('2017-01-01 12:00 am', $dateTime->format('Y-m-d g:i a'));
    }

    /**
     * @test
     *
     * @magentoConfigFixture default/general/locale/timezone Australia/Brisbane
     */
    public function getLocalizedDateAu()
    {
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var View $block */
        $block = $layout->createBlock(View::class);

        $dateTime = $block->getDate('2017-01-01T00:00:01Z');
        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertEquals('2017-01-01 10:00 am', $dateTime->format('Y-m-d g:i a'));
    }
}
