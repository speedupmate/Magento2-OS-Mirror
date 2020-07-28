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

use Magento\Checkout\Model\Session;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Cart extends Template
{
    /**
     * @var Resolver
     */
    private $locale;
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context  $context
     * @param Resolver $locale
     * @param Session  $session
     * @param array    $data
     * @codeCoverageIgnore
     */
    public function __construct(Context $context, Resolver $locale, Session $session, array $data = [])
    {
        parent::__construct($context, $data);
        $this->locale  = $locale;
        $this->session = $session;
    }

    /**
     * Check to see if display on cart is enabled
     *
     * @return bool
     */
    public function showInCart(): bool
    {
        return $this->isSetFlag('klarna/osm/enabled')
            && $this->isSetFlag('klarna/osm/cart_enabled');
    }

    /**
     * Get the locale according to ISO_3166-1 standard
     *
     * @return string
     */
    public function getLocale(): string
    {
        return str_replace('_', '-', $this->locale->getLocale());
    }

    /**
     * Get placement id
     *
     * @return string
     */
    public function getPlacementId(): string
    {
        $placementId = $this->getValue('klarna/osm/cart_placement_select');
        if ($placementId && $placementId === 'other') {
            $placementId = $this->getValue('klarna/osm/cart_placement_other');
        }
        return $placementId;
    }

    /**
     * Get theme (default or dark)
     *
     * @return string
     */
    public function getTheme(): string
    {
        return $this->getValue('klarna/osm/theme');
    }

    /**
     * Get the amount of the purchase formated as an integer `round(amount * 100)`
     *
     * @return int
     */
    public function getPurchaseAmount(): int
    {
        $quote = $this->session->getQuote();
        $price = $quote->getGrandTotal();
        return (int)round($price * 100);
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
