<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidationApi\Api\Data;

use Vertex\Data\AddressInterface as SdkAddressInterface;

/**
 * This is the interface for submission to the API.  For ease the API only takes interfaces the same as the SDK, but the
 * SDK model cannot implement the interface.  Since the SDK model technically isn't API, we re-implement a simple DTO to
 * handle the scenario.
 */
interface AddressInterface extends SdkAddressInterface
{
    /**
     * @param string $city
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setCity($city);

    /**
     * @param string $countryCode Country ID
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setCountry($countryCode);

    /**
     * @param string $region
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setMainDivision($region);

    /**
     * @param string $postalCode
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setPostalCode($postalCode);

    /**
     * @param string[] $streetAddress
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setStreetAddress(array $streetAddress);

    /**
     * @param string $subDivision
     * @return \Vertex\AddressValidationApi\Api\Data\AddressInterface
     */
    public function setSubDivision($subDivision);
}
