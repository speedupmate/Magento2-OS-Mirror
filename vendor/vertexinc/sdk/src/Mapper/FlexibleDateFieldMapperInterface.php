<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper;

use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see FlexibleDateFieldInterface}
 *
 * @api
 */
interface FlexibleDateFieldMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see FlexibleDateFieldInterface}
     *
     * @param \stdClass $map
     * @return FlexibleDateFieldInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see FlexibleDateFieldInterface} into a SOAP compatible object
     *
     * @param FlexibleDateFieldInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(FlexibleDateFieldInterface $object);
}
