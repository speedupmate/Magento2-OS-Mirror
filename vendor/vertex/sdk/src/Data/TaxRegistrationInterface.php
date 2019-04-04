<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/**
 * Represents a tax registration
 *
 * @api
 */
interface TaxRegistrationInterface
{
    /**
     * Retrieve the country code for the tax registration
     *
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Retrieve the imposition type of the registration code
     *
     * e.g. "VAT"
     *
     * @return string|null
     */
    public function getImpositionType();

    /**
     * Retrieve the state, region, or province the code is registered to
     *
     * @return string|null
     */
    public function getMainDivision();

    /**
     * Retrieve the physical locations associated to the registration number
     *
     * @return AddressInterface[]
     */
    public function getPhysicalLocations();

    /**
     * Retrieve the recorded registration number
     *
     * @return string|null
     */
    public function getRegistrationNumber();

    /**
     * Retrieve whether or not the entity has a physical presence in the country attached to the registration code
     *
     * @return bool|null
     */
    public function hasPhysicalPresence();

    /**
     * Set the country code for the tax registration
     *
     * @param string $countryCode 3 character ISO country code
     * @return TaxRegistrationInterface
     */
    public function setCountryCode($countryCode);

    /**
     * Set whether or not the entity has a physical presence in the country attached to the registration code
     *
     * @param bool $hasPhysicalPresence
     * @return TaxRegistrationInterface
     */
    public function setHasPhysicalPresence($hasPhysicalPresence);

    /**
     * Set the imposition type of the registration code
     *
     * e.g. "VAT"
     *
     * @param string $impositionType
     * @return TaxRegistrationInterface
     */
    public function setImpositionType($impositionType);

    /**
     * Set the state, region, or province the code is registered to
     *
     * @param string $mainDivision
     * @return TaxRegistrationInterface
     */
    public function setMainDivision($mainDivision);

    /**
     * Set the physical locations associated to the registration number
     *
     * @param AddressInterface[] $addresses
     * @return TaxRegistrationInterface
     */
    public function setPhysicalLocations(array $addresses);

    /**
     * Set the registration number
     *
     * @param string $registrationNumber
     * @return TaxRegistrationInterface
     */
    public function setRegistrationNumber($registrationNumber);
}
