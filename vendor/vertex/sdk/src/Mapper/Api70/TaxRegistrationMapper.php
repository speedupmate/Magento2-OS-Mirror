<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api70;

use Vertex\Data\TaxRegistrationInterface;
use Vertex\Mapper\Api60\TaxRegistrationMapper as TaxRegistrationMapper60;
use Vertex\Mapper\MapperUtilities;
use Vertex\Mapper\TaxRegistrationMapperInterface;

/**
 * API Level 70 implementation of {@see TaxRegistrationInterface}
 */
class TaxRegistrationMapper implements TaxRegistrationMapperInterface
{
    /** @var TaxRegistrationMapper60 */
    private $parentMapper;

    /** @var MapperUtilities */
    private $utilities;

    /**
     * @param MapperUtilities|null $utilities
     * @param TaxRegistrationMapper60|null $parentMapper
     */
    public function __construct(MapperUtilities $utilities = null, TaxRegistrationMapper60 $parentMapper = null)
    {
        $this->utilities = $utilities ?: new MapperUtilities();
        $this->parentMapper = $parentMapper ?: new TaxRegistrationMapper60();
    }

    /**
     * @inheritDoc
     */
    public function getCountryCodeMaxLength()
    {
        return $this->parentMapper->getCountryCodeMaxLength();
    }

    /**
     * @inheritDoc
     */
    public function getCountryCodeMinLength()
    {
        return $this->parentMapper->getCountryCodeMinLength();
    }

    /**
     * @inheritDoc
     */
    public function validateCountryCode($fieldValue)
    {
        return $this->parentMapper->validateCountryCode($fieldValue);
    }

    /**
     * @inheritDoc
     */
    public function getRegistrationNumberMaxLength()
    {
        return $this->parentMapper->getRegistrationNumberMaxLength();
    }

    /**
     * @inheritDoc
     */
    public function getRegistrationNumberMinLength()
    {
        return $this->parentMapper->getRegistrationNumberMinLength();
    }

    /**
     * @inheritDoc
     */
    public function validateRegistrationNumber($fieldValue)
    {
        return $this->parentMapper->validateRegistrationNumber($fieldValue);
    }

    /**
     * @inheritDoc
     */
    public function build(\stdClass $map)
    {
        $registration = $this->parentMapper->build($map);

        if (isset($map->impositionType)) {
            $registration->setImpositionType($map->impositionType);
        }

        return $registration;
    }

    /**
     * @inheritDoc
     */
    public function map(TaxRegistrationInterface $object)
    {
        $map = $this->parentMapper->map($object);

        $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getImpositionType(),
            'impositionType',
            0,
            40,
            true,
            'Imposition Type'
        );

        return $map;
    }
}
