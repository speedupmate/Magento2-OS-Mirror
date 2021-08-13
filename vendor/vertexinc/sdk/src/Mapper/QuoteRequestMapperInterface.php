<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper;

use Vertex\Exception\ValidationException;
use Vertex\Services\Quote\RequestInterface;

/**
 * SOAP mapping methods for {@see RequestInterface}
 *
 * @api
 */
interface QuoteRequestMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see RequestInterface}
     *
     * @param \stdClass $map
     * @return RequestInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see RequestInterface} into a SOAP compatible object
     *
     * @param RequestInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(RequestInterface $object);

    /**
     * Return maximum character length for location code field
     *
     * @return int
     */
    public function getLocationCodeMaxLength();

    /**
     * Return maximum character length for location code field
     *
     * @return int
     */
    public function getLocationCodeMinLength();

    /**
     * Validates location code field value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateLocationCode($fieldValue);
}
