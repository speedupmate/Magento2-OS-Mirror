<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\TaxRegistry;

use Magento\Framework\App\Cache\StateInterface;
use Vertex\Tax\Model\Cache\Type as CacheType;
use Vertex\Tax\Model\TaxRegistry\CacheStorage;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that cache storage persists data across requests.
 * @magentoAppArea frontend
 */
class CacheStoragePersistenceTest extends TestCase
{
    /** @var StateInterface */
    private $cacheState;

    /** @var CacheStorage */
    private $cacheStorage;

    /**
     * Perform test setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheState = $this->getObject(StateInterface::class);
        $this->cacheStorage = $this->getObject(CacheStorage::class);
    }

    /**
     * Test that cache storage can unset its data.
     * @magentoAppIsolation enabled
     */
    public function testSuccessfulCacheUnset()
    {
        $cacheKey = 'key_to_unset';

        $this->cacheState->setEnabled(CacheType::TYPE_IDENTIFIER, true);
        $this->assertTrue($this->cacheState->isEnabled(CacheType::TYPE_IDENTIFIER));

        $this->assertNull($this->cacheStorage->get($cacheKey), 'Test is not isolated properly');

        $this->assertTrue($this->cacheStorage->set($cacheKey, 'value_to_unset'));
        $this->assertSame('value_to_unset', $this->cacheStorage->get($cacheKey));

        $this->assertTrue($this->cacheStorage->unsetData($cacheKey));
        $this->assertNull($this->cacheStorage->get($cacheKey));
    }

    /**
     * Test that cache storage succeeds when in fallback mode.
     * @magentoAppIsolation enabled
     */
    public function testGenericPersistenceUnderCacheDisablement()
    {
        $this->cacheState->setEnabled(CacheType::TYPE_IDENTIFIER, false);
        $this->assertFalse($this->cacheState->isEnabled(CacheType::TYPE_IDENTIFIER));

        $expectedResult = 'test_value';

        $this->cacheStorage->set('test_key', $expectedResult);
        $actualResult = $this->cacheStorage->get('test_key');

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test that cache storage succeeds when enabled.
     * @magentoAppIsolation enabled
     */
    public function testPersistenceUnderCacheEnablement()
    {
        $this->cacheState->setEnabled(CacheType::TYPE_IDENTIFIER, true);
        $this->assertTrue($this->cacheState->isEnabled(CacheType::TYPE_IDENTIFIER));

        $expectedResult = 'test_value2';

        $this->cacheStorage->set('test_key2', $expectedResult, 300);
        $actualResult = $this->cacheStorage->get('test_key2');

        $this->assertEquals($expectedResult, $actualResult);
    }
}
