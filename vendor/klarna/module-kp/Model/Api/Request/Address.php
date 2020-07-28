<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Model\Api\Request;

use Klarna\Kp\Api\Data\AddressInterface;

class Address implements AddressInterface
{
    use \Klarna\Kp\Model\Api\Export;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $given_name;

    /**
     * @var string
     */
    private $family_name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $street_address;

    /**
     * @var string
     */
    private $street_address2;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $postal_code;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $organization_name;

    /**
     * @param array $data
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
                $this->exports[] = $key;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritDoc}
     */
    public function setGivenName($givenName)
    {
        $this->given_name = $givenName;
    }

    /**
     * {@inheritDoc}
     */
    public function setFamilyName($familyName)
    {
        $this->family_name = $familyName;
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * {@inheritDoc}
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * {@inheritDoc}
     */
    public function setStreetAddress($streetAddress)
    {
        $this->street_address = $streetAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setStreetAddress2($streetAddress)
    {
        $this->street_address2 = $streetAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * {@inheritDoc}
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * {@inheritDoc}
     */
    public function setPostalCode($postalCode)
    {
        $this->postal_code = $postalCode;
    }

    /**
     * {@inheritDoc}
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Set organization/Company name
     *
     * @param string $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organization_name = $organizationName;
    }
}
