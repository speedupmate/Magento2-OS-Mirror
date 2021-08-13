<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\Tax\Model\Config;

/**
 * Disable Vertex message
 *
 * Due to performance concerns, Vertex does not support displaying taxes in catalog prices at this time.  This
 * notification is used to inform the admin user when Vertex is automatically disabled when such a setting is turned on.
 */
class DisableMessage
{
    /** @var array */
    private $affectedScopes = [];

    /** @var Config */
    private $config;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var UrlInterface */
    private $urlBuilder;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Add store to affected stores
     *
     * @param StoreInterface $store
     * @return void
     */
    private function addToAffectedScopes(StoreInterface $store)
    {
        try {
            $website = $this->websiteRepository->getById($store->getWebsiteId());
        } catch (NoSuchEntityException $e) {
            $website = null;
        }
        $websiteName = $website ? $website->getName() : 'Unknown Website';
        $this->affectedScopes[$store->getWebsiteId()] = $websiteName . ' (' . $store->getName() . ')';
    }

    /**
     * Retrieve a list of stores where these incompatible settings exist
     *
     * @return string[]
     */
    public function getAffectedScopes()
    {
        if (!empty($this->affectedScopes)) {
            return $this->affectedScopes;
        }

        foreach ($this->storeManager->getStores(true) as $store) {
            if ($this->isStoreAffected($store->getId())) {
                $this->addToAffectedScopes($store);
            }
        }

        return $this->affectedScopes;
    }

    /**
     * Retrieve the URL for modifying the configuration that has caused this
     *
     * @return string
     */
    private function getManageUrl()
    {
        return $this->urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            ['section' => 'tax', '_fragment' => 'tax_display-head']
        );
    }

    /**
     * Retrieve message text
     *
     * @param string|null $scopeId
     * @param bool $showAffectedStores
     * @return string
     */
    public function getMessage($scopeId = null, $showAffectedStores = false)
    {
        if (empty($this->getAffectedScopes())) {
            return '';
        }

        $scopesToShow = $this->affectedScopes;

        if (!$showAffectedStores && $scopeId !== null) {
            $scopesToShow = [];
            if (isset($this->affectedScopes[$scopeId])) {
                $scopesToShow = [$this->affectedScopes[$scopeId]];
            }
        }

        if (empty($scopesToShow)) {
            return '';
        }
        $html = '<p>'
            . __(
                'Vertex Tax Calculation has been automatically disabled. ' .
                'Display prices in Catalog must be set to "Excluding Tax" to use Vertex.'
            )
            . '</p><p>';
        if ($showAffectedStores) {
            $html .= (count($scopesToShow) > 1 ? __('Stores affected: ') : __('Store affected: '))
                . implode(', ', $scopesToShow)
                . '</p><p>';
        }

        $html .= __(
            'Click here to go to <a href="%1">Price Display Settings</a> and change your settings.',
            $this->getManageUrl())
            . '</p>';

        return $html;
    }

    /**
     * Determine whether or not a store has incompatible settings
     *
     * @param string|null $scopeCode
     * @param $scopeType
     * @return bool
     */
    private function isStoreAffected($scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->config->isVertexActive($scopeCode, $scopeType)
            && $this->config->isDisplayPriceInCatalogEnabled($scopeCode, $scopeType);
    }
}
