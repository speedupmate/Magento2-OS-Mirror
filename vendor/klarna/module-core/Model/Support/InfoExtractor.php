<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model\Support;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class InfoExtractor
{
    /**
     * @var ConfigExtractor
     */
    private $configExtractor;
    /**
     * @var StoreTreeExtractor
     */
    private $storeTreeExtractor;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigExtractor       $configExtractor
     * @param StoreTreeExtractor    $storeTreeExtractor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigExtractor $configExtractor,
        StoreTreeExtractor $storeTreeExtractor,
        StoreManagerInterface $storeManager
    ) {
        $this->configExtractor = $configExtractor;
        $this->storeTreeExtractor = $storeTreeExtractor;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns klarna information
     *
     * @return array
     */
    public function getKlarnaInfo(): array
    {
        return $this->getConfigs('klarna', ['klarna'], ['shared_secret']);
    }

    /**
     * Returns tax information
     *
     * @return array
     */
    public function getTaxInfo(): array
    {
        return $this->getConfigs('tax', ['tax']);
    }

    /**
     * Returns store tree
     *
     * @return array
     */
    public function getStoreTreeInfo(): array
    {
        return $this->storeTreeExtractor->getTree();
    }

    /**
     * Extracts default config and all scope configs
     *
     * @param string $type
     * @param array  $contains
     * @param array  $notContains
     * @return array
     */
    private function getConfigs(string $type, array $contains = [], array $notContains = []): array
    {
        $configs = [];

        $config = $this->configExtractor->getConfig(
            $contains,
            $notContains,
        );
        $configs[$this->getFileName($type, 'default')] = $config;

        $array = [
            ScopeInterface::SCOPE_WEBSITE => $this->storeManager->getWebsites(),
            ScopeInterface::SCOPE_STORE => $this->storeManager->getStores()
        ];

        foreach ($array as $scope => $entries) {
            $scopeConfigs = $this->getScopeConfigs(
                $type,
                $scope,
                $entries,
                $contains,
                $notContains
            );
            foreach ($scopeConfigs as $key => $config) {
                $configs[$key] = $config;
            }
        }

        return $configs;
    }

    /**
     * Extracts scope configs
     *
     * @param string $type
     * @param string $scope
     * @param array  $entries
     * @param array  $contains
     * @param array  $notContains
     * @return array
     */
    private function getScopeConfigs(
        string $type,
        string $scope,
        array $entries,
        array $contains,
        array $notContains
    ): array {
        $configs = [];

        foreach (array_keys($entries) as $id) {
            $fileName = $this->getFileName($type, $scope, $id);
            $configs[$fileName] = $this->configExtractor->getConfig(
                $contains,
                $notContains,
                $scope,
                $id
            );
        }

        return $configs;
    }

    /**
     * Get file name
     *
     * @param string   $type
     * @param string   $scope
     * @param int|null $id
     * @return string
     */
    private function getFileName(string $type, string $scope, int $id = null): string
    {
        $string = $type . '_' . $scope;
        if ($id) {
            $string = $string . '_' . (string) $id;
        }
        return $string;
    }
}
