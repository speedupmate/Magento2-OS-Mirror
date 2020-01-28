<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/**
 * {@inheritDoc}
 */
class TaxRegistration implements TaxRegistrationInterface
{
    /** @var string */
    private $countryCode;

    /** @var bool */
    private $hasPhysicalPresence;

    /** @var string */
    private $impositionType;

    /** @var string */
    private $mainDivision;

    /** @var AddressInterface[] */
    private $physicalLocations = [];

    /** @var string */
    private $registrationNumber;

    /**
     * @inheritDoc
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @inheritDoc
     */
    public function getImpositionType()
    {
        return $this->impositionType;
    }

    /**
     * @inheritDoc
     */
    public function getMainDivision()
    {
        return $this->mainDivision;
    }

    /**
     * @inheritDoc
     */
    public function getPhysicalLocations()
    {
        return $this->physicalLocations;
    }

    /**
     * @inheritDoc
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * @inheritDoc
     */
    public function hasPhysicalPresence()
    {
        return $this->hasPhysicalPresence;
    }

    /**
     * @inheritDoc
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHasPhysicalPresence($hasPhysicalPresence)
    {
        $this->hasPhysicalPresence = $hasPhysicalPresence;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setImpositionType($impositionType)
    {
        $this->impositionType = $impositionType;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMainDivision($mainDivision)
    {
        $this->mainDivision = $mainDivision;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPhysicalLocations(array $addresses)
    {
        foreach ($addresses as $address) {
            if (!$address instanceof AddressInterface) {
                throw new \InvalidArgumentException('Must be an array of AddressInterface');
            }
        }
        $this->physicalLocations = $addresses;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;
        return $this;
    }
}
