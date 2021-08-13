<?php
declare(strict_types=1);

namespace Vertex\Tax\Setup\Patch\Data;

use Magento\Config\Model\Config\Loader;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeInterfaceFactory as ScopeDefaultFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\Tax\Model\Config;

/**
 * Loops through each scope and determines if Vertex should be updated to use the Shipping Origin as the Seller info
 */
class DetermineCurrentConfigForShippingOriginUse implements DataPatchInterface
{
    private const ADDRESS_CONFIG_PATHS = [
        Config::CONFIG_XML_PATH_VERTEX_STREET1,
        Config::CONFIG_XML_PATH_VERTEX_STREET2,
        Config::CONFIG_XML_PATH_VERTEX_CITY,
        Config::CONFIG_XML_PATH_VERTEX_REGION,
        Config::CONFIG_XML_PATH_VERTEX_COUNTRY,
        Config::CONFIG_XML_PATH_VERTEX_POSTAL_CODE,
    ];

    /** @var TypeListInterface */
    private $cacheList;

    /** @var ScopeDefaultFactory */
    private $defaultScopeFactory;

    /** @var Loader */
    private $loader;

    /** @var ConfigResource */
    private $resource;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Loader $loader,
        ConfigResource $resource,
        ScopeDefaultFactory $defaultScopeFactory,
        TypeListInterface $cacheList
    ) {
        $this->storeManager = $storeManager;
        $this->loader = $loader;
        $this->resource = $resource;
        $this->defaultScopeFactory = $defaultScopeFactory;
        $this->cacheList = $cacheList;
    }

    public function apply(): void
    {
        $cacheUpdated = false;
        foreach ($this->getAllScopes() as $scope) {
            if ($this->shouldUpdateConfig($scope->getScopeType(), (int)$scope->getId())) {
                $cacheUpdated = true;
                $this->setUseOriginValue($scope->getScopeType(), (int)$scope->getId());
            }
        }

        if ($cacheUpdated) {
            $this->cacheList->invalidate('CONFIG');
        }
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return ScopeInterface[]
     */
    private function getAllScopes(): array
    {
        return array_merge(
            [$this->defaultScopeFactory->create()],
            $this->storeManager->getWebsites(),
            $this->storeManager->getStores()
        );
    }

    private function setUseOriginValue(string $scopeType, int $scopeId, int $useOrigin = 0): void
    {
        $this->resource->saveConfig(
            Config::CONFIG_XML_PATH_VERTEX_SHIPPING_ORIGIN_SOURCE,
            $useOrigin,
            $scopeType,
            $scopeId
        );
    }

    private function shouldUpdateConfig(string $scopeType, int $scopeId): bool
    {
        $vertexConfig = $this->loader->getConfigByPath('tax/vertex_seller_info', $scopeType, $scopeId, false);

        foreach (static::ADDRESS_CONFIG_PATHS as $vertexPath) {
            if (isset($vertexConfig[$vertexPath])) {
                return true;
            }
        }
        return false;
    }
}
