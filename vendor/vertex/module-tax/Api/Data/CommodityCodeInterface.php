<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Api\Data;

/**
 * Data model representing a Commmodity Code
 *
 * @api
 */
interface CommodityCodeInterface
{
    /**
     * Get commodity code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get commodity code type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set commodity code
     *
     * @param string $code
     * @return \Vertex\Tax\Api\Data\CommodityCodeInterface
     */
    public function setCode(string $code);

    /**
     * Get commodity code type
     *
     * @param string $type
     * @return \Vertex\Tax\Api\Data\CommodityCodeInterface
     */
    public function setType(string $type);
}
