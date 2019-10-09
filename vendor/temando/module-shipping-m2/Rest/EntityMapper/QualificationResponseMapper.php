<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;
use Temando\Shipping\Rest\Response\DataObject\CollectionPoint;
use Temando\Shipping\Rest\Response\DataObject\OrderQualification;
use Temando\Shipping\Webservice\Response\Type\QualificationResponseType;
use Temando\Shipping\Webservice\Response\Type\QualificationResponseTypeFactory;

/**
 * Map API data to application data object
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualificationResponseMapper
{
    /**
     * @var QualificationResponseTypeFactory
     */
    private $qualificationResponseFactory;

    /**
     * @var ShippingExperienceMapper
     */
    private $shippingExperienceMapper;

    /**
     * @var DeliveryLocationMapper
     */
    private $deliveryLocationMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * QualificationResponseMapper constructor.
     * @param QualificationResponseTypeFactory $qualificationResponseFactory
     * @param ShippingExperienceMapper $shippingExperienceMapper
     * @param DeliveryLocationMapper $deliveryLocationMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        QualificationResponseTypeFactory $qualificationResponseFactory,
        ShippingExperienceMapper $shippingExperienceMapper,
        DeliveryLocationMapper $deliveryLocationMapper,
        LoggerInterface $logger
    ) {
        $this->qualificationResponseFactory = $qualificationResponseFactory;
        $this->shippingExperienceMapper = $shippingExperienceMapper;
        $this->deliveryLocationMapper = $deliveryLocationMapper;
        $this->logger = $logger;
    }

    /**
     * @param OrderQualification[] $apiQualifications
     * @return ShippingExperienceInterface[]
     */
    private function mapAddressQualifications(array $apiQualifications)
    {
        $shippingExperiences = [];

        $apiQualifications = array_filter($apiQualifications, function (OrderQualification $apiQualification) {
            return ($apiQualification->getType() === 'shippingMethod');
        });

        foreach ($apiQualifications as $apiQualification) {
            try {
                $shippingExperiences[]= $this->shippingExperienceMapper->map($apiQualification);
            } catch (LocalizedException $exception) {
                continue;
            }
        }

        return $shippingExperiences;
    }

    /**
     * @param OrderQualification[] $apiQualifications
     * @return QuoteCollectionPointInterface[]
     */
    private function mapCollectionPointQualifications(array $apiQualifications)
    {
        $collectionPoints = [];

        $apiQualifications = array_reduce($apiQualifications, function (array $carry, OrderQualification $apiQualification) {
            if ($apiQualification->getType() === 'collectionPointMethod') {
                // extract collection point qualifications
                $apiCollectionPoint = current($apiQualification->getCollectionPoints());
                if (!$apiCollectionPoint instanceof CollectionPoint) {
                    return $carry;
                }

                if (!isset($carry[$apiCollectionPoint->getId()])) {
                    $carry[$apiCollectionPoint->getId()]['location'] = $apiCollectionPoint;
                    $carry[$apiCollectionPoint->getId()]['qualifications'] = [];
                }

                // group qualifications by collection point
                $carry[$apiCollectionPoint->getId()]['qualifications'][] = $apiQualification;
            }

            return $carry;
        }, []);

        foreach ($apiQualifications as $collectionPointId => $data) {
            $apiCollectionPoint = $data['location'];
            $apiCollectionPointQualifications = $data['qualifications'];
            $collectionPoint = $this->deliveryLocationMapper->mapCollectionPoint(
                $apiCollectionPoint,
                $apiCollectionPointQualifications
            );

            if (!empty($collectionPoint->getShippingExperiences())) {
                // only add collection point qualification if valid shipping experiences were found
                $collectionPoints[]= $collectionPoint;
            }
        }

        return $collectionPoints;
    }

    /**
     * @param OrderQualification[] $apiQualifications
     * @return QuotePickupLocationInterface[]
     */
    private function mapPickupLocationQualifications(array $apiQualifications)
    {
        $pickupLocations = [];

        $apiQualifications = array_reduce($apiQualifications, function (array $carry, OrderQualification $apiQualification) {
            if ($apiQualification->getType() === 'clickAndCollectMethod') {
                // extract pickup location qualifications
                $apiLocation = current($apiQualification->getLocations());
                if (!$apiLocation) {
                    return $carry;
                }

                if (!isset($carry[$apiLocation->getId()])) {
                    $carry[$apiLocation->getId()]['location'] = $apiLocation;
                    $carry[$apiLocation->getId()]['qualifications'] = [];
                }

                // group qualifications by pickup location
                $carry[$apiLocation->getId()]['qualifications'][] = $apiQualification;
            }

            return $carry;
        }, []);

        foreach ($apiQualifications as $locationId => $data) {
            $apiLocation = $data['location'];
            $apiLocationQualifications = $data['qualifications'];
            $pickupLocation = $this->deliveryLocationMapper->mapPickupLocation(
                $apiLocation,
                $apiLocationQualifications
            );

            if (!empty($pickupLocation->getShippingExperiences())) {
                // only add pickup location qualification if valid shipping experiences were found
                $pickupLocations[]= $pickupLocation;
            }
        }

        return $pickupLocations;
    }

    /**
     * Create qualification object from platform data.
     *
     * Aggregate experiences by collection point/pickup location.
     *
     * @param OrderQualification[] $apiQualifications
     * @return QualificationResponseType
     */
    public function map(array $apiQualifications)
    {
        if (empty($apiQualifications)) {
            $qualificationResponse = $this->qualificationResponseFactory->create();
            return $qualificationResponse;
        }

        $shippingExperiences = $this->mapAddressQualifications($apiQualifications);
        $collectionPoints = $this->mapCollectionPointQualifications($apiQualifications);
        $pickupLocations = $this->mapPickupLocationQualifications($apiQualifications);

        if (empty($shippingExperiences) && empty($collectionPoints) && empty($pickupLocations)) {
            $this->logger->error(__('No applicable shipping cost found in webservice response.'));
        }

        $qualificationResponse = $this->qualificationResponseFactory->create([
            'shippingExperiences' => $shippingExperiences,
            'collectionPoints' => $collectionPoints,
            'pickupLocations' => $pickupLocations,
        ]);

        return $qualificationResponse;
    }
}
