<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api60;

use Vertex\Data\AddressInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Data\TaxRegistrationInterface;
use Vertex\Mapper\AddressMapperInterface;
use Vertex\Mapper\MapperUtilities;
use Vertex\Mapper\TaxRegistrationMapperInterface;

/**
 * API Level 60 implementation of {@see TaxRegistrationInterface}
 */
class TaxRegistrationMapper implements TaxRegistrationMapperInterface
{
    /** @var int Maximum characters allowed for country code */
    const COUNTRY_CODE_MAX = 3;
    /** @var int Minimum characters allowed for country code */
    const COUNTRY_CODE_MIN = 2;
    /** @var int Maximum characters allowed for registration number */
    const REGISTRATION_NUMBER_MAX = 40;
    /** @var int Minimum characters allowed for registration number */
    const REGISTRATION_NUMBER_MIN = 0;

    /** @var AddressMapper */
    private $addressMapper;

    /** @var MapperUtilities */
    private $utilities;

    /**
     * @param MapperUtilities|null $utilities
     * @param AddressMapperInterface|null $addressMapper
     */
    public function __construct(MapperUtilities $utilities = null, AddressMapperInterface $addressMapper = null)
    {
        $this->utilities = $utilities ?: new MapperUtilities();
        $this->addressMapper = $addressMapper ?: new AddressMapper();
    }

    /**
     * @inheritDoc
     */
    public function getCountryCodeMaxLength()
    {
        return static::COUNTRY_CODE_MAX;
    }

    /**
     * @inheritDoc
     */
    public function getCountryCodeMinLength()
    {
        return static::COUNTRY_CODE_MIN;
    }

    /**
     * @inheritDoc
     */
    public function validateCountryCode($fieldValue)
    {
        // @TODO Implement validateCountryCode() method.
    }

    /**
     * @inheritDoc
     */
    public function getRegistrationNumberMaxLength()
    {
        return static::REGISTRATION_NUMBER_MAX;
    }

    /**
     * @inheritDoc
     */
    public function getRegistrationNumberMinLength()
    {
        return static::REGISTRATION_NUMBER_MIN;
    }

    /**
     * @inheritDoc
     */
    public function validateRegistrationNumber($fieldValue)
    {
        // @TODO Implement validateRegistrationNumber() method.
    }

    /**
     * @inheritdoc
     */
    public function build(\stdClass $map)
    {
        $registration = new TaxRegistration();

        $this->buildPhysicalLocations($map, $registration);

        if (isset($map->TaxRegistrationNumber)) {
            $registration->setRegistrationNumber(
                $map->TaxRegistrationNumber instanceof \stdClass
                    ? $map->TaxRegistrationNumber->_
                    : $map->TaxRegistrationNumber
            );
        }

        if (isset($map->isoCountryCode)) {
            $registration->setCountryCode($map->isoCountryCode);
        }

        if (isset($map->mainDivision)) {
            $registration->setMainDivision($map->mainDivision);
        }

        if (isset($map->hasPhysicalPresenceIndicator)) {
            $registration->setHasPhysicalPresence((bool)$map->hasPhysicalPresenceIndicator);
        }

        return $registration;
    }

    /**
     * @inheritDoc
     */
    public function map(TaxRegistrationInterface $object)
    {
        $map = new \stdClass();

        if (count($object->getPhysicalLocations())) {
            $map->PhysicalLocation = array_map(
                function (AddressInterface $address) {
                    return $this->addressMapper->map($address);
                },
                $object->getPhysicalLocations()
            );
        }

        $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getRegistrationNumber(),
            'TaxRegistrationNumber',
            $this->getRegistrationNumberMinLength(),
            $this->getRegistrationNumberMaxLength(),
            true,
            'Registration Number'
        );

        $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getCountryCode(),
            'isoCountryCode',
            $this->getCountryCodeMinLength(),
            $this->getCountryCodeMaxLength(),
            true,
            'Country Code'
        );

        $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getMainDivision(),
            'mainDivision',
            1,
            60,
            true,
            'Main Division'
        );

        if ($object->hasPhysicalPresence() !== null) {
            $map->hasPhysicalPresenceIndicator = $object->hasPhysicalPresence();
        }

        return $map;
    }

    /**
     * Turn a SOAP response object into an array of {@see AddressInterface}
     *
     * @param \stdClass $map
     * @param TaxRegistrationInterface $registration
     * @return void
     */
    private function buildPhysicalLocations(\stdClass $map, TaxRegistrationInterface $registration)
    {
        $rawLocations = [];
        if (isset($map->PhysicalLocation)) {
            $rawLocations = is_array($map->PhysicalLocation) ? $map->PhysicalLocation : [$map->PhysicalLocation];
        }
        $physicalLocations = array_map(
            function ($rawLocation) {
                return $this->addressMapper->build($rawLocation);
            },
            $rawLocations
        );
        $registration->setPhysicalLocations($physicalLocations);
    }
}
