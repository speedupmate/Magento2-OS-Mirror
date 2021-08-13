<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper;

use stdClass;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see FlexibleCodeFieldInterface}
 *
 * @api
 */
interface FlexibleCodeFieldMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see FlexibleCodeFieldInterface}
     *
     * @param stdClass $map
     * @return FlexibleCodeFieldInterface
     */
    public function build(stdClass $map);

    /**
     * Retrieve the maximum value allowed for the ID Field
     *
     * @return int
     */
    public function getIdValueMaximum();

    /**
     * Retrieve the minimum value allowed for the ID Field
     *
     * @return int
     */
    public function getIdValueMinimum();

    /**
     * Retrieve the maximum character length for the Value Field
     *
     * @return int
     */
    public function getValueMaximumLength();

    /**
     * Retrieve the minimum length allowed for the Value Field
     *
     * @return mixed
     */
    public function getValueMinimumLength();

    /**
     * Validate the content of the Value field
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateValue($fieldValue);

    /**
     * Validate the content of the ID field
     *
     * @param int $fieldId
     * @return true
     * @throws ValidationException
     */
    public function validateId($fieldId);

    /**
     * Turn an instance of {@see FlexibleCodeFieldInterface} into a SOAP compatible object
     *
     * @param FlexibleCodeFieldInterface $object
     * @return stdClass
     * @throws ValidationException
     */
    public function map(FlexibleCodeFieldInterface $object);
}
