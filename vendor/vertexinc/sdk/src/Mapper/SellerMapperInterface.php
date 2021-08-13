<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper;

use Vertex\Data\SellerInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see SellerInterface}
 *
 * @api
 */
interface SellerMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see SellerInterface}
     *
     * @param \stdClass $map
     * @return SellerInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see SellerInterface} into a SOAP compatible object
     *
     * @param SellerInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(SellerInterface $object);

    /**
     * Return maximum length allowed for the company code value
     *
     * @return int
     */
    public function getCompanyCodeMaxLength();

    /**
     * Return minimum length allowed for the company code value
     *
     * @return int
     */
    public function getCompanyCodeMinLength();

    /**
     * Validates company code value
     *
     * @param string $fieldValue
     * @return true
     * @throws ValidationException
     */
    public function validateCompanyCode($fieldValue);
}
