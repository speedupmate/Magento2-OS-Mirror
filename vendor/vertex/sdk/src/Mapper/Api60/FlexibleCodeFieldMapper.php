<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper\Api60;

use stdClass;
use Vertex\Data\FlexibleCodeField;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Mapper\FlexibleCodeFieldMapperInterface;
use Vertex\Mapper\MapperUtilities;

/** @inheritDoc */
class FlexibleCodeFieldMapper implements FlexibleCodeFieldMapperInterface
{
    /** Whether or not the id field is optional */
    const ID_IS_OPTIONAL = false;

    /** Maximum ID for a Flexible Code Field */
    const ID_MAX = 25;

    /** Minimum ID for a Flexible Code Field */
    const ID_MIN = 1;

    /** Human readable name for the id field in exceptions */
    const ID_NAME = 'Field ID';

    /** Whether or not the value field is optional */
    const VALUE_IS_OPTIONAL = false;

    /** Maximum Length for a Flexible Code Value */
    const VALUE_MAX_LENGTH = 40;

    /** Minimum Length for a Flexible Code Value */
    const VALUE_MIN_LENGTH = 1;

    /** Human readable name for the value field in exceptions */
    const VALUE_NAME = 'Field Value';

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
    public function build(stdClass $map)
    {
        $flexibleCodeField = new FlexibleCodeField();
        $flexibleCodeField->setFieldId($map->fieldId);
        $flexibleCodeField->setFieldValue($map->_);

        return $flexibleCodeField;
    }

    /**
     * @inheritDoc
     */
    public function getIdValueMaximum()
    {
        return static::ID_MAX;
    }

    /**
     * @inheritDoc
     */
    public function getIdValueMinimum()
    {
        return static::ID_MIN;
    }

    /**
     * @inheritDoc
     */
    public function getValueMaximumLength()
    {
        return static::VALUE_MAX_LENGTH;
    }

    /**
     * @inheritDoc
     */
    public function getValueMinimumLength()
    {
        return static::VALUE_MIN_LENGTH;
    }

    /**
     * @inheritDoc
     */
    public function map(FlexibleCodeFieldInterface $object)
    {
        $flexibleField = new stdClass();
        $flexibleField = $this->utilities->addToMapWithLengthValidation(
            $flexibleField,
            $object->getFieldValue(),
            '_',
            $this->getValueMinimumLength(),
            $this->getValueMaximumLength(),
            static::VALUE_IS_OPTIONAL,
            static::VALUE_NAME
        );
        $flexibleField = $this->utilities->addToMapWithIntegerValidation(
            $flexibleField,
            $object->getFieldId(),
            'fieldId',
            $this->getIdValueMinimum(),
            $this->getIdValueMaximum(),
            static::ID_IS_OPTIONAL,
            static::ID_NAME
        );

        return $flexibleField;
    }

    /**
     * @inheritDoc
     */
    public function validateId($fieldId)
    {
        return $this->utilities->assertNotNull($fieldId, static::ID_IS_OPTIONAL, static::ID_NAME)
            && $this->utilities->assertInteger($fieldId, static::ID_NAME)
            && $this->utilities->assertBetween($fieldId, $this->getIdValueMinimum(), $this->getIdValueMaximum());
    }

    /**
     * @inheritDoc
     */
    public function validateValue($fieldValue)
    {
        return $this->utilities->assertNotNull($fieldValue, static::VALUE_IS_OPTIONAL, static::VALUE_NAME)
            && $this->utilities->assertLength(
                $fieldValue,
                $this->getValueMinimumLength(),
                $this->getValueMaximumLength()
            );
    }
}
