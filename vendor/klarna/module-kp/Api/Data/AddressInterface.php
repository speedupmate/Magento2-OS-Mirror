<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Api\Data;

interface AddressInterface extends ApiObjectInterface
{
    /**
     * Set title. Possible values Mr or Mrs
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Set given name. [REQUIRED field]
     *
     * @param string $givenName
     */
    public function setGivenName($givenName);

    /**
     * Set family name. [REQUIRED field]
     *
     * @param string $familyName
     */
    public function setFamilyName($familyName);

    /**
     * Set e-mail address. [REQUIRED field]
     *
     * @param string $email
     */
    public function setEmail($email);

    /**
     * Set phone number.
     *
     * @param string $phone
     */
    public function setPhone($phone);

    /**
     * Set street address, first line. [REQUIRED field]
     *
     * @param string $streetAddress
     */
    public function setStreetAddress($streetAddress);

    /**
     * Set street address, second line.
     *
     * @param string $streetAddress
     */
    public function setStreetAddress2($streetAddress);

    /**
     * Set city. [REQUIRED field]
     *
     * @param string $city
     */
    public function setCity($city);

    /**
     * Set region
     *
     * @param string $region
     */
    public function setRegion($region);

    /**
     * Set postal/post code. [REQUIRED field]
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode);

    /**
     * Set country(ISO 3166 alpha+2). [REQUIRED field]
     *
     * @param string $country
     */
    public function setCountry($country);
}
