<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config;

/**
 * Serialization tool for Flexible Field configuration
 */
class FlexibleFieldSerializer
{
    /**
     * Serialize flexible field configuration for storage in database
     *
     * @param array|null $value
     * @return string
     */
    public function serialize($value = null)
    {
        if (is_array($value)) {
            $encode = json_encode($value, true);
            return $encode ? $encode : '';
        }
        return '';
    }

    /**
     * Unserialize flexible fields configuration for use in admin panel
     *
     * @param string $value
     * @return array
     */
    public function unserialize($value)
    {
        if (is_string($value) && !empty($value)) {
            return json_decode($value, true);
        }

        return [];
    }
}
