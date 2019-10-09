<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Authentication;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Temando\Shipping\Rest\AuthAdapter;
use Temando\Shipping\Rest\Authentication;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;
use Zend\Http\Request;

/**
 * RefreshTokenTest
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @magentoAppArea adminhtml
 */
class RefreshTokenTest extends AbstractBackendController
{
    /**
     * The resource used to authorize action
     *
     * @var string
     */
    protected $resource = 'Magento_Sales::sales';

    /**
     * The uri at which to access the controller
     *
     * @var string
     */
    protected $uri = 'backend/temando/authentication/token';

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
     * Assert 403 when accessing token controller by other means than XmlHttpRequest.
     *
     * @test
     */
    public function nonAjaxRequestForbidden()
    {
        $this->getRequest()->setMethod(Request::METHOD_GET);

        $this->dispatch($this->uri);

        $this->assertTrue($this->getResponse()->isForbidden());
    }

    /**
     * Assert input exception being thrown when refreshing token with missing credentials.
     *
     * @test
     *
     * @magentoDataFixture createExpiredApiToken
     */
    public function refreshTokenRequestFailure()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessageRegExp('/^"[\w]+" is required./');

        /** @var \Zend\Http\Headers $headers */
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);

        $this->dispatch($this->uri);
    }

    /**
     * Assert that the token remains unchanged if valid token exists.
     *
     * @test
     *
     * @magentoDataFixture createApiToken
     */
    public function refreshTokenNotNecessary()
    {
        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();

        // assert adapter method to init new session is not executed
        $adapterMock->expects($this->never())->method('startSession');

        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);
        Bootstrap::getObjectManager()->addSharedInstance($auth, Authentication::class);

        /** @var \Zend\Http\Headers $headers */
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);
        $this->dispatch($this->uri);

        // assert controller returns values from cache
        $responseJson = json_decode($this->getResponse()->getBody());
        $this->assertEquals('token', $responseJson->temando_api_token);
        $this->assertEquals('2038-01-19T03:03:33.000Z', $responseJson->temando_api_token_ttl);
    }

    /**
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     * @magentoConfigFixture default/carriers/temando/bearer_token_expiry 1999-01-19T03:03:33.000Z
     * @magentoDataFixture createExpiredApiToken
     */
    public function refreshTokenRequestSuccess()
    {
        $sessionToken = 'updated-token';
        $sessionTokenExpiry = '2038-01-19T03:03:33.000Z';

        $authResponse = new DataObject([
            'attributes' => new DataObject([
                'session_token' => $sessionToken,
                'expiry' => $sessionTokenExpiry,
            ]),
        ]);
        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();

        // assert adapter method to init new session is executed and will return new token response
        $adapterMock->expects($this->once())->method('startSession')->willReturn($authResponse);

        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);
        Bootstrap::getObjectManager()->addSharedInstance($auth, Authentication::class);

        /** @var \Zend\Http\Headers $headers */
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);
        $this->dispatch($this->uri);

        // assert controller returns cached api response
        $responseJson = json_decode($this->getResponse()->getBody());
        $this->assertEquals($sessionToken, $responseJson->temando_api_token);
        $this->assertEquals($sessionTokenExpiry, $responseJson->temando_api_token_ttl);
    }

    /**
     * @magentoDataFixture createApiToken
     */
    public function testAclHasAccess()
    {
        $authMock = $this->getMockBuilder(Authentication::class)
            ->setMethods(['connect', 'getSessionToken', 'getSessionTokenExpiry'])
            ->disableOriginalConstructor()
            ->getMock();
        Bootstrap::getObjectManager()->addSharedInstance($authMock, Authentication::class);

        /** @var \Zend\Http\Headers $headers */
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);

        parent::testAclHasAccess();
    }

    /**
     * @magentoDataFixture createApiToken
     */
    public function testAclNoAccess()
    {
        /** @var \Zend\Http\Headers $headers */
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->getRequest()->setHeaders($headers);

        parent::testAclNoAccess();
    }
}
