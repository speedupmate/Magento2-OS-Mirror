<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

/**
 * Business logic for determining if Vertex should be used for tax calculation
 */
class VertexUsageDeterminer
{
    /** @var AddressDeterminer */
    private $addressDeterminer;

    /** @var Config */
    private $config;

    /** @var CountryGuard */
    private $countryGuard;

    /**
     * @param Config $config
     * @param CountryGuard $countryGuard
     * @param AddressDeterminer $addressDeterminer
     */
    public function __construct(Config $config, CountryGuard $countryGuard, AddressDeterminer $addressDeterminer)
    {
        $this->config = $config;
        $this->countryGuard = $countryGuard;
        $this->addressDeterminer = $addressDeterminer;
    }

    /**
     * Determine whether or not to use Vertex to calculate taxes for an address
     *
     * @param string|null $storeCode
     * @param AddressInterface|null $address
     * @param int|null $customerId
     * @param bool $checkCalculation
     * @return bool
     */
    public function shouldUseVertex(
        $storeCode = null,
        $address = null,
        ?int $customerId = null,
        $checkCalculation = false
    ) {
        if (!$this->config->isVertexActive($storeCode)
            || ($checkCalculation && !$this->config->isTaxCalculationEnabled($storeCode))
        ) {
            return false;
        }
        if ($address !== null && !($address instanceof AddressInterface || $address instanceof QuoteAddressInterface)) {
            throw new \InvalidArgumentException(
                '$address must be a Customer or Quote Address.  Is: '
                // gettype() used for debug output and not for checking types
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                . (is_object($address) ? get_class($address) : gettype($address))
            );
        }

        if ($this->config->isDisplayPriceInCatalogEnabled($storeCode)) {
            return false;
        }

        $billingAddress = $this->addressDeterminer->determineAdministrativeDestination($address, $customerId);
        $billingCountry = $billingAddress ? $billingAddress->getCountryId() : false;
        $shippingAddress = $this->addressDeterminer->determineDestination($address, $customerId);
        $shippingCountry = $shippingAddress ? $shippingAddress->getCountryId() : false;

        $hasAddress = ($billingAddress !== null && $billingCountry) || ($shippingAddress !== null && $shippingCountry);
        if (!$hasAddress) {
            return false;
        }

        if ($shippingCountry && !$this->countryGuard->isCountryIdServiceableByVertex($shippingCountry)) {
            return false;
        }

        if (!$shippingCountry && $billingCountry
            && !$this->countryGuard->isCountryIdServiceableByVertex($billingCountry)) {
            return false;
        }

        return true;
    }
}
