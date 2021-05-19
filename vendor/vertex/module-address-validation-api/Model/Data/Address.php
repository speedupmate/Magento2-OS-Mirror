<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidationApi\Model\Data;

use InvalidArgumentException;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;

/**
 * @api
 */
class Address implements AddressInterface
{
    private $city;
    private $country;
    private $mainDivision;
    private $postalCode;
    private $streetAddress = [];
    private $subDivision;

    public function getCity()
    {
        return $this->city;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getMainDivision()
    {
        return $this->mainDivision;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    public function getSubDivision()
    {
        return $this->subDivision;
    }

    public function setCity($city)
    {
        if (!is_string($city)) {
            throw new InvalidArgumentException('$city must be of type string');
        }
        $this->city = $city;
        return $this;
    }

    public function setCountry($countryCode)
    {
        if (!is_string($countryCode)) {
            throw new InvalidArgumentException('$countryCode must be of type string');
        }
        $this->country = $countryCode;
        return $this;
    }

    public function setMainDivision($region)
    {
        if (!is_string($region)) {
            throw new InvalidArgumentException('$region must be of type string');
        }
        $this->mainDivision = $region;
        return $this;
    }

    public function setPostalCode($postalCode)
    {
        if (!is_string($postalCode)) {
            throw new InvalidArgumentException('$postalCode must be of type string');
        }
        $this->postalCode = $postalCode;
        return $this;
    }

    public function setStreetAddress(array $streetAddress)
    {
        array_walk(
            $streetAddress,
            static function ($line) {
                if (!is_string($line)) {
                    throw new InvalidArgumentException('$streetAddress must be an array of strings');
                }
            }
        );
        $this->streetAddress = array_values($streetAddress);
        return $this;
    }

    public function setSubDivision($subDivision)
    {
        if (!is_string($subDivision)) {
            throw new InvalidArgumentException('$subDivision must be of type string');
        }
        $this->subDivision = $subDivision;
        return $this;
    }
}
