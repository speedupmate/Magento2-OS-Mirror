<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\DataObject;

use Temando\Shipping\Rest\Response\Fields\OrderQualificationAttributes;

/**
 * Temando API Order Qualification Response Type
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderQualification extends AbstractResource
{
    /**
     * @var \Temando\Shipping\Rest\Response\Fields\OrderQualificationAttributes
     */
    private $attributes;

    /**
     * @var string[]
     */
    private $experienceIds = [];

    /**
     * @var \Temando\Shipping\Rest\Response\DataObject\Location[]
     */
    private $locations = [];

    /**
     * @var \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[]
     */
    private $collectionPoints = [];

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\OrderQualificationAttributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\OrderQualificationAttributes $attributes
     * @return void
     */
    public function setAttributes(OrderQualificationAttributes $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return string[]
     */
    public function getExperienceIds()
    {
        return $this->experienceIds;
    }

    /**
     * @param string[] $experienceIds
     * @return void
     */
    public function setExperienceIds(array $experienceIds)
    {
        $this->experienceIds = $experienceIds;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[]
     */
    public function getCollectionPoints()
    {
        return $this->collectionPoints;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[] $collectionPoints
     * @return void
     */
    public function setCollectionPoints(array $collectionPoints)
    {
        $this->collectionPoints = $collectionPoints;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\DataObject\Location[]
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\DataObject\Location[] $locations
     * @return void
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}
