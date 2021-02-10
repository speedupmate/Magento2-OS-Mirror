<?php
/**
 * This file is part of the Klarna Onsitemessaging module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Onsitemessaging\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Header extends Template
{
    /**
     * Check to see if Onsitemessaging is enabled or not
     *
     * @return bool
     */
    public function isOsmEnabled(): bool
    {
        return $this->isSetFlag('klarna/osm/enabled');
    }

    /**
     * Get UUID data_id
     *
     * @return string|null
     */
    public function getDataId(): ?string
    {
        return $this->getValue('klarna/osm/data_id');
    }

    /**
     * Get JS URL for including Klarna Onsite Messaging on page
     *
     * @return string
     */
    public function getJsUrl(): string
    {
        if ($this->isSetFlag('klarna/api/test_mode')) {
            return sprintf('https://%s-library.playground.klarnaservices.com/lib.js', $this->getEndpoint());
        }
        return sprintf('https://%s-library.klarnaservices.com/lib.js', $this->getEndpoint());
    }

    /**
     * Get endpoint to use (na, eu, oc), depending on market
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        $version = $this->getValue('klarna/api/api_version');
        switch ($version) {
            case "dach_v3":
            case "uk":
            case "nl":
            case "kp_eu":
                $endpoint = 'eu';
                break;
            case "na":
            case "kp_na":
                $endpoint = 'na';
                break;
            case "kp_oc":
                $endpoint = 'oc';
                break;
            default:
                $endpoint = 'eu';
        }
        return $endpoint;
    }

    /**
     * Wrapper around `$this->_scopeConfig->isSetFlag` that ensures store scope is checked
     *
     * @param string $path
     * @return bool
     */
    private function isSetFlag(string $path): bool
    {
        return $this->_scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
    }

    /**
     * Wrapper around `$this->_scopeConfig->getValue` that ensures store scope is checked
     *
     * @param string $path
     * @return mixed
     */
    private function getValue(string $path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
    }
}
