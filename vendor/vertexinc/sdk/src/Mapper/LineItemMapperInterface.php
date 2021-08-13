<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper;

use Vertex\Data\LineItemInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see LineItemInterface}
 *
 * @api
 */
interface LineItemMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see LineItemInterface}
     *
     * @param \stdClass $map
     * @return LineItemInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see LineItemInterface} into a SOAP compatible object
     *
     * @param LineItemInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(LineItemInterface $object);

    /**
     * Retrieve the maximum length allowed for the Value Field
     *
     * @return int
     */
    public function getProductCodeMaxLength();

    /**
     * Retrieve the minimum length allowed for the Value Field
     *
     * @return int
     */
    public function getProductCodeMinLength();

    /**
     * Validate the content of the value field
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateProductCode($fieldValue);

    /**
     * Retrieve maximum length allowed for the product tax class name
     *
     * @return int
     */
    public function getProductTaxClassNameMaxLength();

    /**
     * Retrieve minimum length allowed for the product tax class name
     *
     * @return int
     */
    public function getProductTaxClassNameMinLength();

    /**
     * Validate product tax class name value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateProductTaxClassName($fieldValue);
}
