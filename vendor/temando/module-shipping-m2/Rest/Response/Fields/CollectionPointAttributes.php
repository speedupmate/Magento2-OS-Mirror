<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Fields;

use Temando\Shipping\Rest\Response\Fields\CollectionPoint\Constraints;
use Temando\Shipping\Rest\Response\Fields\Generic\Value;

/**
 * Temando API Collection Point Resource Object Attributes
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CollectionPointAttributes
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var \Temando\Shipping\Rest\Response\Fields\LocationAttributes
     */
    private $location;

    /**
     * @var string[]
     */
    private $integrationServiceIds;

    /**
     * @var \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities
     */
    private $capabilities;

    /**
     * @var \Temando\Shipping\Rest\Response\Fields\Generic\Value
     */
    private $distance;

    /**
     * @var \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Constraints
     */
    private $constraints;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     * @return void
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\LocationAttributes
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\LocationAttributes $location
     * @return void
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string[]
     */
    public function getIntegrationServiceIds()
    {
        return $this->integrationServiceIds;
    }

    /**
     * @param string[] $integrationServiceIds
     * @return void
     */
    public function setIntegrationServiceIds($integrationServiceIds)
    {
        $this->integrationServiceIds = $integrationServiceIds;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Capabilities $capabilities
     * @return void
     */
    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\Generic\Value
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\Generic\Value $distance
     * @return void
     */
    public function setDistance(Value $distance)
    {
        $this->distance = $distance;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Constraints
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\CollectionPoint\Constraints $constraints
     * @return void
     */
    public function setConstraints(Constraints $constraints)
    {
        $this->constraints = $constraints;
    }
}
