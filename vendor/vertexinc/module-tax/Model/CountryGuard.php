<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model;

use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for preventing tax calculation on unsupported countries
 */
class CountryGuard
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Determine if an Order can be serviced by Vertex
     *
     * @param Order $order
     * @return bool
     */
    public function isOrderServiceableByVertex(Order $order)
    {
        if ($order->getIsVirtual() || !$order->getShippingAddress()) {
            $address = $order->getBillingAddress();
        } else {
            $address = $order->getShippingAddress();
        }

        return $address && $this->isCountryIdServiceableByVertex($address->getCountryId(), $order->getStoreId());
    }

    /**
     * Determine if a country can be serviced by Vertex
     *
     * @param string $countryId
     * @return bool
     */
    public function isCountryIdServiceableByVertex(
        $countryId,
        $scopeCode = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        return in_array($countryId, $this->config->getAllowedCountries($scopeCode, $scopeType), false);
    }
}
