<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

/**
 * Determine whether or not we should attempt tax calculation based off the completedness of the address.
 *
 * To calculate any tax, we need at minimum a country.  If the country is the United States, we also require
 * a region.
 */
class IncompleteAddressDeterminer
{
    public function isIncompleteAddress(AddressInterface $address = null): bool
    {
        if ($address === null) {
            return true;
        }
        if ($address->getCountryId() === null) {
            return true;
        }
        if ($address->getPostcode() === null
            && $address->getRegionId() === null
            && $this->isIncompleteRegion($address->getRegion())
        ) {
            return true;
        }
        return false;
    }

    public function isIncompleteQuoteAddress(QuoteAddressInterface $address = null): bool
    {
        if ($address === null) {
            return true;
        }
        if ($address->getCountryId() === null) {
            return true;
        }
        if ($address->getCountryId() === 'US'
            && $address->getRegionId() === null
            && $address->getRegionCode() === null
            && $address->getRegion() === null
            && $address->getPostcode() === null
        ) {
            return true;
        }
        return false;
    }

    private function isIncompleteRegion(RegionInterface $region = null): bool
    {
        return $region === null
            || ($region->getRegion() === null
                && $region->getRegionId() === null
                && $region->getRegionCode() === null);
    }
}
