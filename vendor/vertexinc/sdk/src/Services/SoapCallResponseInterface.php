<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Services;

/**
 * Represents a response from an HTTP call
 */
interface SoapCallResponseInterface
{
    /**
     * Retrieve the time it took to call the SOAP endpoint, in milliseconds
     *
     * This does includes neither mapping nor the instantiation of the soap object
     *
     * @return int
     */
    public function getHttpCallTime();

    /**
     * Set the time it took to call the SOAP endpoint, in milliseconds
     *
     * @param int $milliseconds
     * @return SoapCallResponseInterface
     */
    public function setHttpCallTime($milliseconds);
}
