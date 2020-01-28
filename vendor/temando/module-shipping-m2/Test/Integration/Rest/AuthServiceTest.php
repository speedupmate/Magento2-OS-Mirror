<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\Storage;
use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Response\DataObject\Session;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;

/**
 * Temando Session Handling Test
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AuthServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BackendSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storageMock;

    /**
     * @var SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionManager;

    protected function setUp()
    {
        parent::setUp();

        $this->storageMock = $this->getMockBuilder(Storage::class)
                                  ->setMethods(['getData', 'setData'])
                                  ->getMock();
        $this->sessionManager = Bootstrap::getObjectManager()->create(
            SessionManagerInterface::class,
            ['storage' => $this->storageMock]
        );
    }

    /**
     * @return string[]
     */
    public function invalidCredentialsDataProvider()
    {
        return [
            'no_credentials' => [null, null],
            'no_account_id' => ['23', null],
            'no_bearer_token' => [null, '808'],
        ];
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
     * Assert token being requested from API if there is no expiry date available.
     *
     * @test
     *
     * @magentoDataFixture createExpiredApiToken
     */
    public function sessionTokenExpiryDateUnavailable()
    {
        $currentTokenExpiry = null;

        $newSessionToken = 'foo';
        $newSessionTokenExpiry = '2038';

        $newSessionResponseAttributes = new \Temando\Shipping\Rest\Response\Fields\SessionAttributes();
        $newSessionResponseAttributes->setSessionToken($newSessionToken);
        $newSessionResponseAttributes->setExpiry($newSessionTokenExpiry);
        $newSessionResponse = new Session();
        $newSessionResponse->setAttributes($newSessionResponseAttributes);

        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('startSession')
            ->willReturn($newSessionResponse);

        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);

        $auth->connect('foo', 'bar');
    }

    /**
     * Assert AuthenticationException being thrown when API returns error.
     *
     * @test
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     *
     * @magentoDataFixture createExpiredApiToken
     */
    public function sessionTokenRefreshFails()
    {
        $exceptionMessage = 'error foo';

        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('startSession')
            ->willThrowException(new AdapterException($exceptionMessage));

        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);

        $auth->connect('foo', 'bar');
    }

    /**
     * @test
     *
     * @magentoDataFixture createExpiredApiToken
     */
    public function sessionTokenExpired()
    {
        $newSessionToken = 'foo';
        $newSessionTokenExpiry = '2038';

        $newSessionResponseAttributes = new \Temando\Shipping\Rest\Response\Fields\SessionAttributes();
        $newSessionResponseAttributes->setSessionToken($newSessionToken);
        $newSessionResponseAttributes->setExpiry($newSessionTokenExpiry);
        $newSessionResponse = new Session();
        $newSessionResponse->setAttributes($newSessionResponseAttributes);

        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('startSession')
            ->willReturn($newSessionResponse);

        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);

        $auth->connect('foo', 'bar');
    }

    /**
     * @test
     * @dataProvider invalidCredentialsDataProvider
     * @expectedException \Magento\Framework\Exception\InputException
     *
     * @magentoDataFixture createExpiredApiToken
     *
     * @param string $bearerToken
     * @param string $accountId
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function credentialsMissing($bearerToken, $accountId)
    {
        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class);

        $auth->connect($bearerToken, $accountId);
    }

    /**
     * @test
     *
     * @magentoDataFixture createApiToken
     */
    public function sessionTokenValid()
    {
        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['startSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->never())
            ->method('startSession');

        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'session' => $this->sessionManager,
            'apiAdapter' => $adapterMock,
        ]);

        $auth->connect('foo', 'bar');
    }

    /**
     * @test
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture createApiToken
     */
    public function disconnect()
    {
        $adapterMock = $this->getMockBuilder(AuthAdapter::class)
            ->setMethods(['endSession'])
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('endSession');

        /** @var Authentication $auth */
        $auth = Bootstrap::getObjectManager()->create(Authentication::class, [
            'apiAdapter' => $adapterMock
        ]);

        $auth->disconnect();

        // after disconnect
        $this->assertEmpty($auth->getSessionToken());
        $this->assertEmpty($auth->getSessionTokenExpiry());
    }
}
