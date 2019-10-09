<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Sales\Model\Order;

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

        return $address && $this->isCountryIdServiceableByVertex($address->getCountryId());
    }

    /**
     * Determine if a country can be serviced by Vertex
     *
     * @param string $countryId
     * @return bool
     */
    public function isCountryIdServiceableByVertex($countryId)
    {
        return in_array($countryId, $this->config->getAllowedCountries(), false);
    }
}
