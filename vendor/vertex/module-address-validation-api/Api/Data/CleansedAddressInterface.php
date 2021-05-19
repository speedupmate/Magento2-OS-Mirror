<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidationApi\Api\Data;

/**
 * What we want to output to the API contains more data than what we want to bring in.  Chiefly, we want to ensure we're
 * sending Magento data out (instead of Vertex data). That means sending Region IDs, and 2-character ISO country codes.
 */
interface CleansedAddressInterface
{
    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string;

    /**
     * @return string|null
     */
    public function getCountryName(): ?string;

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string;

    /**
     * @return int|null
     */
    public function getRegionId(): ?int;

    /**
     * @return string|null
     */
    public function getRegionName(): ?string;

    /**
     * @return string[]
     */
    public function getStreetAddress(): array;

    /**
     * Retrieve the regional sub division, such as a county or parish
     *
     * @return string|null
     */
    public function getSubDivision(): ?string;

    /**
     * @param string $city
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setCity(string $city): self;

    /**
     * @param string $countryCode
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setCountryCode(string $countryCode): self;

    /**
     * @param string $countryName
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setCountryName(string $countryName): self;

    /**
     * @param string $postalCode
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setPostalCode(string $postalCode): self;

    /**
     * @param int $regionId
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setRegionId(int $regionId): self;

    /**
     * @param string $regionName
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setRegionName(string $regionName): self;

    /**
     * @param string[] $street
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setStreetAddress(array $street): self;

    /**
     * Set the regional sub division (such as a county or province)
     *
     * @param string $subDivision
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface
     */
    public function setSubDivision(string $subDivision): self;
}
