<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;

class IncompleteAddressDeterminer
{
    /**
     * Determine whether or not we should attempt tax calculation based off the completedness of the address.
     *
     * To calculate any tax, we need at minimum a country.  If the country is the United States, we also require
     * a region.
     */
    public function isIncompleteAddress(AddressInterface $address = null): bool
    {
        return $address === null || (
                $address->getCountryId() === null
                || ($address->getCountryId() === 'US'
                    && ($address->getRegionId() === null
                        && $this->isIncompleteRegion($address->getRegion()))));
    }

    private function isIncompleteRegion(RegionInterface $region = null): bool
    {
        return $region === null
            || ($region->getRegion() === null
                && $region->getRegionId() === null
                && $region->getRegionCode() === null);
    }
}
