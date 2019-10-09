<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Cache;

use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\TaxRegistry\StorageInterface;

/**
 * Storage utility for processor attribute cycle.
 */
class ProcessorKeyManager
{
    /** @var Serializer */
    private $serializer;

    /** @var StorageInterface */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(
        StorageInterface $storage,
        Serializer $serializer
    ) {
        $this->storage = $storage;
        $this->serializer = $serializer;
    }

    /**
     * Generate a unique ID for processors
     *
     * @param array $processors
     * @return string
     */
    public function createProcessorsHash(array $processors)
    {
        foreach ($processors as $key => $processor) {
            $processors[$key]['processor'] = get_class($processor['processor']);
        }

        return sha1(\json_encode($processors));
    }

    /**
     * Generate a unique ID for each attribute
     *
     * @param string $code
     * @return string
     */
    public function createAttributeHash($code)
    {
        return sha1('_ATTRIBUTE_' . $code);
    }

    /**
     * Retrieve the key for the given item.
     *
     * @param string
     * @return mixed
     */
    public function get($key)
    {
        $result = null;
        try {
            $fields = $this->storage->get($key);
            if ($fields === null) {
                return null;
            }
            return $this->serializer->unserialize($fields);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Write the given quote item ID to storage.
     *
     * @param string $cacheKey
     * @param FlexFieldProcessableAttribute[] $attributes
     * @return void
     */
    public function set($cacheKey, $attributes)
    {
        $this->storage->unsetData($cacheKey);
        $value = $this->serializer->serialize($attributes);
        $this->storage->set($cacheKey, $value, null);
    }
}
