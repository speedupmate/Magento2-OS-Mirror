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

use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class StoreTreeExtractor
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Extracts the complete store tree
     *
     * @return array
     */
    public function getTree(): array
    {
        $tree = [];

        $websites = $this->storeManager->getWebsites();
        /** @var Website $website */
        foreach ($websites as $website) {
            $groups = $website->getGroups();
            /** @var Group $group */
            foreach ($groups as $group) {
                $stores = $group->getStores();
                /** @var Store $store */
                foreach ($stores as $store) {
                    $tree = $this->addItem($tree, $website, $group, $store);
                }
            }
        }

        return $tree;
    }

    /**
     * Adds item to tree and returns new tree
     *
     * @param array   $tree
     * @param Website $website
     * @param Group   $group
     * @param Store   $store
     * @return array
     */
    private function addItem(
        array $tree,
        Website $website,
        Group $group,
        Store $store
    ): array {
        $websiteCode = $website->getCode();
        if (!isset($tree[$websiteCode])) {
            $tree[$websiteCode] = $this->createNode($website->getData());
        }

        $groupCode = $group->getCode();
        if (!isset($tree[$websiteCode]['children'][$groupCode])) {
            $tree[$websiteCode]['children'][$groupCode] = $this->createNode($group->getData());
        }

        $storeCode = $store->getCode();
        $tree[$websiteCode]['children'][$groupCode]['children'][$storeCode] =
            $this->createNode($store->getData());

        return $tree;
    }

    /**
     * Creates tree node
     *
     * @param array $data
     * @return array[]
     */
    private function createNode(array $data): array
    {
        return [
            'data' => $data,
            'children' => []
        ];
    }
}
