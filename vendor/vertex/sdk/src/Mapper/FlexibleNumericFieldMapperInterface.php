<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Mapper;

use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Exception\ValidationException;

/**
 * SOAP mapping methods for {@see FlexibleNumericFieldInterface}
 *
 * @api
 */
interface FlexibleNumericFieldMapperInterface
{
    /**
     * Turn a SOAP response object into an instance of {@see FlexibleNumericFieldInterface}
     *
     * @param \stdClass $map
     * @return FlexibleNumericFieldInterface
     */
    public function build(\stdClass $map);

    /**
     * Turn an instance of {@see FlexibleNumericFieldInterface} into a SOAP compatible object
     *
     * @param FlexibleNumericFieldInterface $object
     * @return \stdClass
     * @throws ValidationException
     */
    public function map(FlexibleNumericFieldInterface $object);
}
