<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;

/**
 * Provides access to a list of attributes that may be processed
 *
 * @api
 * @since 3.2.0
 */
interface ProcessorInterface
{
    /**
     * Retrieve all available attributes
     *
     * @return FlexFieldProcessableAttribute[] Indexed by flex field attribute code
     */
    public function getAttributes();
}
