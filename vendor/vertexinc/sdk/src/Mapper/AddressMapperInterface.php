<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper;

use Vertex\Data\AddressInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see AddressInterface}
 *
 * @api
 */
interface AddressMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see AddressInterface}
     *
     * @param \stdClass $map
     * @return AddressInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see AddressInterface} into a SOAP compatible format
     *
     * @param AddressInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(AddressInterface $object);

    /**
     * Retrieve maximum characters for city field
     *
     * @return int
     */
    public function getCityMaxLength();

    /**
     * Retrieve minimum characters for city field
     *
     * @return int
     */
    public function getCityMinLength();

    /**
     * Validate city value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateCity($fieldValue);

    /**
     * Retrieve maximum characters for country field
     *
     * @return int
     */
    public function getCountryMaxLength();

    /**
     * Retrieve minimum characters for country field
     *
     * @return int
     */
    public function getCountryMinLength();

    /**
     * Validate country value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateCountry($fieldValue);

    /**
     * Retrieve maximum characters for main division field
     *
     * @return int
     */
    public function getMainDivisionMaxLength();

    /**
     * Retrieve minimum characters for main division field
     *
     * @return int
     */
    public function getMainDivisionMinLength();

    /**
     * Validate main division value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateMainDivision($fieldValue);

    /**
     * Retrieve maximum characters for each street address field
     *
     * @return int
     */
    public function getStreetAddressMaxLength();

    /**
     * Retrieve minimum characters for each street address field
     *
     * @return int
     */
    public function getStreetAddressMinLength();

    /**
     * Validate street address value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateStreetAddress($fieldValue);

    /**
     * Retrieve maximum characters for postal code field
     *
     * @return int
     */
    public function getPostalCodeMaxLength();

    /**
     * Retrieve minimum characters for postal code field
     *
     * @return int
     */
    public function getPostalCodeMinLength();

    /**
     * Validate postal code value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validatePostalCode($fieldValue);
}
