<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Cache;

/**
 * Handle data exchange serialization for StorageInterface.
 */
class Serializer
{
    /**
     * Convert from serialized to array or string format
     *
     * @param array|string $value
     * @return string
     */
    public function unserialize($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($this->isSerialized($v)) {
                    $result[$k] = $this->unserialize($v);
                }
            }
        }

        return \unserialize($value, ['allow_classes' => false]);
    }

    /**
     * Check if value is serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        if (getType($value) === 'object') {
            return false;
        }

        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

    /**
     * Convert from serialized format
     *
     * @param string|array|object $value
     * @return string|array|object
     */
    public function serialize($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (!$this->isSerialized($v)) {
                    $attributes[$k] = $this->serialize($v);
                }
            }
        }
        if ($value instanceof \Closure) {
            $value = $value();
        }

        return \serialize($value);
    }
}
