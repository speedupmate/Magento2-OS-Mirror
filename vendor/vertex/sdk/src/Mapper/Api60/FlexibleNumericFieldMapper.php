<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api60;

use Vertex\Data\FlexibleNumericField;
use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\FlexibleNumericFieldMapperInterface;
use Vertex\Mapper\MapperUtilities;

/** @inheritDoc */
class FlexibleNumericFieldMapper implements FlexibleNumericFieldMapperInterface
{
    /** Minimum ID for a Flexible Numeric Field */
    const ID_MIN = 1;

    /** Maximum ID for a Flexible Numeric Field */
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

    /**
     * Turn a SOAP response object into an instance of {@see FlexibleNumericFieldInterface}
     *
     * @param \stdClass $map
     * @return FlexibleNumericFieldInterface
     */
    public function build(\stdClass $map)
    {
        $flexibleField = new FlexibleNumericField();
        $flexibleField->setFieldId($map->fieldId);
        $flexibleField->setFieldValue($map->_);

        return $flexibleField;
    }

    /**
     * Turn an instance of {@see FlexibleNumericFieldInterface} into a SOAP compatible object
     *
     * @param FlexibleNumericFieldInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(FlexibleNumericFieldInterface $object)
    {
        $flexibleField = new \stdClass();
        $flexibleField = $this->utilities->addToMapWithDecimalValidation(
            $flexibleField,
            $object->getFieldValue(),
            '_',
            PHP_INT_MIN,
            PHP_INT_MAX,
            false,
            'Field Value'
        );
        $flexibleField = $this->utilities->addToMapWithIntegerValidation(
            $flexibleField,
            $object->getFieldId(),
            'fieldId',
            self::ID_MIN,
            self::ID_MAX,
            false,
            'Field ID'
        );

        return $flexibleField;
    }
}
