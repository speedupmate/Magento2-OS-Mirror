<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidationApi\Model\Data;

use InvalidArgumentException;
use Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface;

/**
 * @api
 */
class CleansedAddress implements CleansedAddressInterface
{
    /** @var string|null */
    private $city;

    /** @var string|null */
    private $countryCode;

    /** @var string|null */
    private $countryName;

    /** @var string|null */
    private $postalCode;

    /** @var int|null */
    private $regionId;

    /** @var string|null */
    private $regionName;

    /** @var string[] */
    private $streetAddress;

    /** @var string|null */
    private $subDivision;

    public function __construct(
        ?string $countryCode = null,
        ?string $countryName = null,
        ?string $postalCode = null,
        ?string $regionName = null,
        ?int $regionId = null,
        ?string $city = null,
        array $streetAddress = [],
        ?string $subDivision = null
    ) {
        $this->countryCode = $countryCode;
        $this->countryName = $countryName;
        $this->postalCode = $postalCode;
        $this->regionName = $regionName;
        $this->regionId = $regionId;
        array_walk(
            $streetAddress,
            static function ($line) {
                if (!is_string($line)) {
                    throw new InvalidArgumentException('$streetAddress must be an array of strings');
                }
            }
        );
        $this->streetAddress = array_values($streetAddress);
        $this->city = $city;
        $this->subDivision = $subDivision;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getRegionId(): ?int
    {
        return $this->regionId;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    /**
     * @return string[]
     */
    public function getStreetAddress(): array
    {
        return $this->streetAddress;
    }

    public function getSubDivision(): ?string
    {
        return $this->subDivision;
    }

    public function setCity(string $city): CleansedAddressInterface
    {
        $this->city = $city;
        return $this;
    }

    public function setCountryCode(string $countryCode): CleansedAddressInterface
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function setCountryName(string $countryName): CleansedAddressInterface
    {
        $this->countryName = $countryName;
        return $this;
    }

    public function setPostalCode(string $postalCode): CleansedAddressInterface
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function setRegionId(int $regionId): CleansedAddressInterface
    {
        $this->regionId = $regionId;
        return $this;
    }

    public function setRegionName(string $regionName): CleansedAddressInterface
    {
        $this->regionName = $regionName;
        return $this;
    }

    /**
     * @param string[] $street
     */
    public function setStreetAddress(array $street): CleansedAddressInterface
    {
        array_walk(
            $street,
            static function ($line) {
                if (!is_string($line)) {
                    throw new InvalidArgumentException('$street must be an array of strings');
                }
            }
        );
        $this->streetAddress = array_values($street);
        return $this;
    }

    public function setSubDivision(string $subDivision): CleansedAddressInterface
    {
        $this->subDivision = $subDivision;
        return $this;
    }
}
