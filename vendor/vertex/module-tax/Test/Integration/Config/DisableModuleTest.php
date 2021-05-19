<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\Config;

use Magento\Store\Model\Store;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Config\DisableMessage;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that module is disabled
 */
class DisableModuleTest extends TestCase
{
    /**
     * Ensure that the Vertex module is disabled when changed in admin settings
     *
     * @return void
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoDbIsolation enabled
     */
    public function testEnable()
    {
        /** @var Config $config */
        $config = $this->getObjectManager()->get(Config::class);
        $this->assertTrue($config->isVertexActive(), 'enable Vertex module not working');
    }

    /**
     * Ensure that module is automatically disabled when display price is "included"
     *
     * @return void
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/display/type 2
     * @magentoDbIsolation enabled
     */
    public function testAutomaticDisable()
    {
        /** @var Config $config */
        $config = $this->getObjectManager()->get(Config::class);
        $this->assertTrue(
            $config->isVertexActive(),
            'vertex not enabled'
        );
        $this->assertTrue(
            $config->isDisplayPriceInCatalogEnabled(),
            'automatic disable not working'
        );

        /** @var DisableMessage $disableMessage */
        $disableMessage = $this->getObject(DisableMessage::class);
        $this->assertNotEmpty(
            $disableMessage->getMessage(),
            'disable message not showing'
        );
        $this->assertStringContainsString(
            'Default Store View',
            (string)$disableMessage->getMessage(Store::DEFAULT_STORE_ID, true),
            'disable message not showing affect stores'
        );
    }
}
