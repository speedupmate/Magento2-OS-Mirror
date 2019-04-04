<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\RelationsInterface;

/**
 * Provides object manager configuration when in compiled mode
 */
class Compiled implements ConfigInterface
{
    /**
     * @var array
     */
    private $arguments;

    /**
     * @var array
     */
    private $virtualTypes;

    /**
     * @var array
     */
    private $preferences;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->arguments = $data['arguments'];
        $this->virtualTypes = $data['instanceTypes'];
        $this->preferences = $data['preferences'];
    }

    /**
     * Set class relations
     *
     * @param RelationsInterface $relations
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setRelations(RelationsInterface $relations)
    {
    }

    /**
     * Set configuration cache instance
     *
     * @param ConfigCacheInterface $cache
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCache(ConfigCacheInterface $cache)
    {
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array
     */
    public function getArguments($type)
    {
        if (array_key_exists($type, $this->arguments)) {
            if (is_string($this->arguments[$type])) {
                $this->arguments[$type] = unserialize($this->arguments[$type]);
            } else if ($this->arguments[$type] === null) {
                $this->arguments[$type] = [];
            }
            return $this->arguments[$type];
        } else {
            return null;
        }
    }

    /**
     * Check whether type is shared
     *
     * @param string $type
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isShared($type)
    {
        return true;
    }

    /**
     * Retrieve instance type
     *
     * @param string $instanceName
     * @return mixed
     */
    public function getInstanceType($instanceName)
    {
        if (isset($this->virtualTypes[$instanceName])) {
            return $this->virtualTypes[$instanceName];
        }
        return $instanceName;
    }

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function getPreference($type)
    {
        if (isset($this->preferences[$type])) {
            return $this->preferences[$type];
        }
        return $type;
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     */
    public function extend(array $configuration)
    {
        $this->arguments = isset($configuration['arguments'])
            ? array_replace($this->arguments, $configuration['arguments'])
            : $this->arguments;
        $this->virtualTypes = isset($configuration['instanceTypes'])
            ? array_replace($this->virtualTypes, $configuration['instanceTypes'])
            : $this->virtualTypes;
        $this->preferences = isset($configuration['preferences'])
            ? array_replace($this->preferences, $configuration['preferences'])
            : $this->preferences;
    }

    /**
     * Retrieve all virtual types
     *
     * @return string
     */
    public function getVirtualTypes()
    {
        return $this->virtualTypes;
    }

    /**
     * Returns list on preferences
     *
     * @return array
     */
    public function getPreferences()
    {
        return $this->preferences;
    }
}
