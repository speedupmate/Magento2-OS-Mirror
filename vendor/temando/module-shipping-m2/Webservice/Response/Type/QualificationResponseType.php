<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Webservice\Response\Type;

use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;

/**
 * Temando Order Qualification Result
 *
 * @package Temando\Shipping\Webservice
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualificationResponseType
{
    /**
     * @var ShippingExperienceInterface[]
     */
    private $shippingExperiences;

    /**
     * @var QuoteCollectionPointInterface[]
     */
    private $collectionPoints;

    /**
     * @var QuotePickupLocationInterface[]
     */
    private $pickupLocations;

    /**
     * QualificationResponseType constructor.
     * @param ShippingExperienceInterface[] $shippingExperiences
     * @param QuoteCollectionPointInterface[] $collectionPoints
     * @param QuotePickupLocationInterface[] $pickupLocations
     */
    public function __construct(
        array $shippingExperiences = [],
        array $collectionPoints = [],
        array $pickupLocations = []
    ) {
        $this->shippingExperiences = $shippingExperiences;
        $this->collectionPoints = $collectionPoints;
        $this->pickupLocations = $pickupLocations;
    }

    /**
     * @return ShippingExperienceInterface[]
     */
    public function getShippingExperiences()
    {
        return $this->shippingExperiences;
    }

    /**
     * @return QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints()
    {
        return $this->collectionPoints;
    }

    /**
     * @return QuotePickupLocationInterface[]
     */
    public function getPickupLocations()
    {
        return $this->pickupLocations;
    }
}
