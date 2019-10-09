<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api60;

use Vertex\Data\FlexibleDateField;
use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Mapper\FlexibleDateFieldMapperInterface;
use Vertex\Mapper\MapperUtilities;

/** @inheritDoc */
class FlexibleDateFieldMapper implements FlexibleDateFieldMapperInterface
{
    /** Minimum ID for a Flexible Date Field */
    const ID_MIN = 1;

    /** Maximum ID for a Flexible Date Field */
    const ID_MAX = 5;

    /** @var MapperUtilities */
    private $utilities;

    /**
     * @param MapperUtilities|null $utilities
     */
    public function __construct(MapperUtilities $utilities = null)
    {
        $this->utilities = $utilities ?: new MapperUtilities();
    }

    /** @inheritDoc */
    public function build(\stdClass $map)
    {
        $flexibleField = new FlexibleDateField();
        $flexibleField->setFieldId($map->fieldId);
        $flexibleField->setFieldValue(
            \class_exists(\DateTimeImmutable::class)
                ? new \DateTimeImmutable($map->_)
                : new \DateTime($map->_)
        );

        return $flexibleField;
    }

    /** @inheritDoc */
    public function map(FlexibleDateFieldInterface $object)
    {
        $flexibleField = new \stdClass();
        $flexibleField = $this->utilities->addToMapWithIntegerValidation(
            $flexibleField,
            $object->getFieldId(),
            'fieldId',
            self::ID_MIN,
            self::ID_MAX,
            false,
            'Field ID'
        );
        $flexibleField = $this->utilities->addToMapWithDateValidation(
            $flexibleField,
            $object->getFieldValue(),
            '_',
            false,
            'Field Value'
        );

        return $flexibleField;
    }
}
