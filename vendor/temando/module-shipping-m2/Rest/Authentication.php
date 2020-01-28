<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Temando\Shipping\Rest\Adapter\AuthenticationApiInterface;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\AuthRequestFactory;
use Temando\Shipping\Webservice\Config\WsConfigInterface;

/**
 * Temando REST API Authentication
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Authentication implements AuthenticationInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var WsConfigInterface
     */
    private $config;

    /**
     * @var AuthenticationApiInterface
     */
    private $apiAdapter;

    /**
     * @var AuthRequestFactory
     */
    private $authRequestFactory;

    /**
     * Authentication constructor.
     * @param CacheInterface $cache
     * @param Json $serializer
     * @param WsConfigInterface $config
     * @param AuthenticationApiInterface $apiAdapter
     * @param AuthRequestFactory $authRequestFactory
     */
    public function __construct(
        CacheInterface $cache,
        Json $serializer,
        WsConfigInterface $config,
        AuthenticationApiInterface $apiAdapter,
        AuthRequestFactory $authRequestFactory
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->apiAdapter = $apiAdapter;
        $this->authRequestFactory = $authRequestFactory;
    }

    /**
     * Check if session token is invalid
     *
     * @return bool
     */
    private function isSessionTokenExpired(): bool
    {
        return !(bool) $this->cache->load(self::CACHE_KEY_SESSION_TOKEN);
    }

    /**
     * Save Temando API token to cache.
     *
     * @param string $sessionToken
     * @param string $sessionTokenExpiry
     * @return void
     */
    private function setSession(string $sessionToken, string $sessionTokenExpiry): void
    {
        $data = $this->serializer->serialize([
            self::DATA_KEY_SESSION_TOKEN => $sessionToken,
            self::DATA_KEY_SESSION_TOKEN_EXPIRY => $sessionTokenExpiry
        ]);

        // let the cache expire 20mins before the session token actually expires.
        $threshold = 1200;
        $cacheLifetime = strtotime($sessionTokenExpiry) - time() - $threshold;

        $this->cache->save($data, self::CACHE_KEY_SESSION_TOKEN, [], $cacheLifetime);
    }

    /**
     * Remove Temando API token from cache.
     *
     * @return void
     */
    private function unsetSession(): void
    {
        $this->cache->remove(self::CACHE_KEY_SESSION_TOKEN);
    }

    /**
     * Refresh bearer token.
     *
     * For future use, bearer tokens do currently not expire.
     *
     * @param string $username
     * @param string $password
     * @throws AuthenticationException
     * @throws InputException
     */
    public function authenticate($username, $password): void
    {
        if (!$username) {
            throw InputException::requiredField('username');
        }

        if (!$password) {
            throw InputException::requiredField('password');
        }

        try {
            $requestType = $this->authRequestFactory->create([
                'scope' => self::AUTH_SCOPE_ADMIN,
                'username' => $username,
                'password' => $password,
            ]);

            $this->apiAdapter->startSession($requestType);
        } catch (AdapterException $e) {
            $msg = 'API connection could not be established. Please check your credentials (%1).';
            throw new AuthenticationException(__($msg, $e->getMessage()), $e);
        }
    }

    /**
     * Refresh session token if expired.
     *
     * @param string $accountId
     * @param string $bearerToken
     * @return void
     * @throws AuthenticationException
     * @throws InputException
     */
    public function connect($accountId, $bearerToken): void
    {
        if (!$this->isSessionTokenExpired()) {
            return;
        }

        if (!$accountId) {
            throw InputException::requiredField('accountId');
        }

        if (!$bearerToken) {
            throw InputException::requiredField('bearerToken');
        }

        try {
            $requestType = $this->authRequestFactory->create([
                'scope'       => self::AUTH_SCOPE_ADMIN,
                'accountId'   => $accountId,
                'bearerToken' => $bearerToken,
            ]);
            $session = $this->apiAdapter->startSession($requestType);
        } catch (AdapterException $e) {
            $msg = 'API connection could not be established. Please check your credentials (%1).';
            throw new AuthenticationException(__($msg, $e->getMessage()), $e);
        }

        // save session info in admin/customer session
        $this->setSession(
            $session->getAttributes()->getSessionToken(),
            $session->getAttributes()->getExpiry()
        );

        // save merchant's api endpoint in config
        if ($session->getAttributes()->getApiUrl()) {
            $this->config->saveApiEndpoint($session->getAttributes()->getApiUrl());
        } else {
            $this->config->saveApiEndpoint($this->config->getSessionEndpoint());
        }
    }

    /**
     * Delete session token.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->apiAdapter->endSession();
        $this->unsetSession();
    }

    /**
     * Force refresh session token.
     *
     * @param string $accountId
     * @param string $bearerToken
     * @return void
     * @throws AuthenticationException
     * @throws InputException
     */
    public function reconnect($accountId, $bearerToken): void
    {
        $this->disconnect();
        $this->connect($accountId, $bearerToken);
    }

    /**
     * Read Temando Session Token.
     *
     * @return string
     */
    public function getSessionToken(): string
    {
        $cache = $this->cache->load(self::CACHE_KEY_SESSION_TOKEN);
        if ($cache) {
            $data = $this->serializer->unserialize($cache);
            return $data[self::DATA_KEY_SESSION_TOKEN] ?? '';
        }

        return '';
    }

    /**
     * Read Temando Session Token Expiry Date Time.
     *
     * @return string
     */
    public function getSessionTokenExpiry(): string
    {
        $cache = $this->cache->load(self::CACHE_KEY_SESSION_TOKEN);
        if ($cache) {
            $data = $this->serializer->unserialize($cache);
            return $data[self::DATA_KEY_SESSION_TOKEN_EXPIRY] ?? '';
        }

        return '';
    }
}
