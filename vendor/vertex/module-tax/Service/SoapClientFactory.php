<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Service;

use \Vertex\Utility\SoapClientFactory as Factory;

/**
 * Create an instance of SoapClient
 */
class SoapClientFactory extends Factory
{
    /**
     * @inheritdoc
     */
    public function create($wsdl, array $options = [])
    {
        return parent::create($wsdl, $options);
    }
}
