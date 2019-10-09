<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Test\Integration\Fixture;

use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Rest\AuthenticationInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * ApiTokenFixture
 *
 * @package  Temando\Shipping\Test
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
final class ApiTokenFixture
{
    /**
     * Create a "valid" api session token to focus on actual platform response processing.
     */
    public static function createValidToken()
    {
        /** @var CacheInterface $cache */
        $cache = Bootstrap::getObjectManager()->get(CacheInterface::class);

        $cacheData = [
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN => 'token',
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN_EXPIRY => '2038-01-19T03:03:33.000Z'
        ];

        /** @var Json $serializer */
        $serializer = Bootstrap::getObjectManager()->get(Json::class);
        $cache->save($serializer->serialize($cacheData), AuthenticationInterface::CACHE_KEY_SESSION_TOKEN);
    }

    /**
     * Create an "invalid" api session token.
     */
    public static function createInvalidToken()
    {
        /** @var CacheInterface $cache */
        $cache = Bootstrap::getObjectManager()->get(CacheInterface::class);

        $cacheData = [
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN => 'token',
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN_EXPIRY => '1999-01-19T03:03:33.000Z'
        ];

        /** @var Json $serializer */
        $serializer = Bootstrap::getObjectManager()->get(Json::class);
        $cache->save($serializer->serialize($cacheData), AuthenticationInterface::CACHE_KEY_SESSION_TOKEN);
    }

    /**
     * Create a "valid" api session token with expired cache lifetime.
     */
    public static function createExpiredToken()
    {
        /** @var CacheInterface $cache */
        $cache = Bootstrap::getObjectManager()->get(CacheInterface::class);

        $cacheData = [
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN => 'token',
            AuthenticationInterface::DATA_KEY_SESSION_TOKEN_EXPIRY => '2038-01-19T03:03:33.000Z'
        ];

        /** @var Json $serializer */
        $serializer = Bootstrap::getObjectManager()->get(Json::class);
        $cache->save(
            $serializer->serialize($cacheData),
            AuthenticationInterface::CACHE_KEY_SESSION_TOKEN,
            [],
            -1
        );
    }

    /**
     * Unset cached api session token.
     */
    public static function rollbackToken()
    {
        /** @var CacheInterface $cache */
        $cache = Bootstrap::getObjectManager()->get(CacheInterface::class);
        $cache->remove(AuthenticationInterface::CACHE_KEY_SESSION_TOKEN);
    }
}
