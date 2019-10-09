<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper;

use Vertex\Data\CustomerInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see CustomerInterface}
 *
 * @api
 */
interface CustomerMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see CustomerInterface}
     *
     * @param \stdClass $map
     * @return CustomerInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see CustomerInterface} into a SOAP compatible object
     *
     * @param CustomerInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(CustomerInterface $object);

    /**
     * Return maximum length of Customer tax class name value
     *
     * @return int
     */
    public function getCustomerTaxClassNameMaxLength();

    /**
     * Return maximum length of Customer tax class name value
     *
     * @return int
     */
    public function getCustomerTaxClassNameMinLength();

    /**
     * Validates Customer tax class name value
     *
     * @param string $fieldName
     * @return true
     * @throws ValidationException
     */
    public function validateCustomerTaxClassName($fieldName);

    /**
     * Returns maximum length for Customer code value
     *
     * @return int
     */
    public function getCustomerCodeMaxLength();

    /**
     * Returns minimum length for Customer code value
     *
     * @return int
     */
    public function getCustomerCodeMinLength();

    /**
     * Validates Customer code value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateCustomerCode($fieldValue);
}
