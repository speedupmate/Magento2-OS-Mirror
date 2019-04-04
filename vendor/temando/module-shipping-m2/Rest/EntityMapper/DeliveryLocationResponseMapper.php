<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterfaceFactory;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterfaceFactory;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;
use Temando\Shipping\Rest\Response\DataObject\OrderQualification;
use Temando\Shipping\Rest\Response\DataObject\CollectionPoint;
use Temando\Shipping\Rest\Response\DataObject\Location;
use Temando\Shipping\Rest\Response\Fields\Generic\Value;
use Temando\Shipping\Rest\Response\Fields\Location\OpeningHours;
use Magento\Shipping\Helper\Carrier;

/**
 * Map API data to application data object
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.temando.com/
 */
class DeliveryLocationResponseMapper
{
    /**
     * @var OpeningHoursMapper
     */
    private $openingHoursMapper;

    /**
     * @var ShippingExperienceMapper
     */
    private $shippingExperienceMapper;

    /**
     * @var Carrier
     */
    private $unitConverter;

    /**
     * @var QuoteCollectionPointInterfaceFactory
     */
    private $collectionPointFactory;

    /**
     * @var QuotePickupLocationInterfaceFactory
     */
    private $pickupLocationFactory;

    /**
     * DeliveryLocationResponseMapper constructor.
     * @param OpeningHoursMapper $openingHoursMapper
     * @param ShippingExperienceMapper $shippingExperienceMapper
     * @param Carrier $unitConverter
     * @param QuoteCollectionPointInterfaceFactory $collectionPointFactory
     * @param QuotePickupLocationInterfaceFactory $pickupLocationFactory
     */
    public function __construct(
        OpeningHoursMapper $openingHoursMapper,
        ShippingExperienceMapper $shippingExperienceMapper,
        Carrier $unitConverter,
        QuoteCollectionPointInterfaceFactory $collectionPointFactory,
        QuotePickupLocationInterfaceFactory $pickupLocationFactory
    ) {
        $this->openingHoursMapper = $openingHoursMapper;
        $this->shippingExperienceMapper = $shippingExperienceMapper;
        $this->unitConverter = $unitConverter;
        $this->collectionPointFactory = $collectionPointFactory;
        $this->pickupLocationFactory = $pickupLocationFactory;
    }

    /**
     * Normalize delivery location distance to meters.
     *
     * @param Value|null $apiDistance
     * @return int|null
     */
    private function mapDistance(?Value $apiDistance): ?int
    {
        if (!$apiDistance instanceof Value) {
            return null;
        }

        $targetUnit = \Zend_Measure_Length::METER;
        $sourceValue = $apiDistance->getValue();
        switch ($apiDistance->getUnit()) {
            case 'mi':
                $sourceUnit = \Zend_Measure_Length::MILE;
                break;
            default:
                $sourceUnit = \Zend_Measure_Length::KILOMETER;
        }

        $value = (int) $this->unitConverter->convertMeasureDimension($sourceValue, $sourceUnit, $targetUnit);

        return $value;
    }

    /**
     * Transform opening hours format.
     *
     * @param OpeningHours|null $apiHours
     * @return string[][]
     */
    private function mapOpeningHours(OpeningHours $apiHours = null): array
    {
        if ($apiHours instanceof OpeningHours) {
            return $this->openingHoursMapper->map($apiHours);
        } else {
            return [
                'general' => [],
                'specific' => [],
            ];
        }
    }

    /**
     * @param OrderQualification[] $apiQualifications
     * @return ShippingExperienceInterface[]
     */
    private function mapShippingExperiences(array $apiQualifications): array
    {
        $shippingExperiences = [];

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
     * Create collection point object from platform data.
     *
     * @param CollectionPoint $apiCollectionPoint
     * @param OrderQualification[] $apiQualifications
     * @return QuoteCollectionPointInterface
     */
    public function mapCollectionPoint(
        CollectionPoint $apiCollectionPoint,
        array $apiQualifications
    ): QuoteCollectionPointInterface {
        $apiLocation = $apiCollectionPoint->getAttributes()->getLocation();
        $apiAddress = $apiLocation->getAddress();
        $distance = $this->mapDistance($apiCollectionPoint->getAttributes()->getDistance());
        $openingHours = $this->mapOpeningHours($apiLocation->getOpeningHours());
        $shippingExperiences = $this->mapShippingExperiences($apiQualifications);

        $collectionPoint = $this->collectionPointFactory->create(['data' => [
            QuoteCollectionPointInterface::COLLECTION_POINT_ID => $apiCollectionPoint->getId(),
            QuoteCollectionPointInterface::NAME => $apiCollectionPoint->getAttributes()->getName(),
            QuoteCollectionPointInterface::COUNTRY => $apiAddress->getCountryCode(),
            QuoteCollectionPointInterface::REGION => $apiAddress->getAdministrativeArea(),
            QuoteCollectionPointInterface::POSTCODE => $apiAddress->getPostalCode(),
            QuoteCollectionPointInterface::CITY => $apiAddress->getLocality(),
            QuoteCollectionPointInterface::STREET => $apiAddress->getLines(),
            QuoteCollectionPointInterface::DISTANCE => $distance,
            QuoteCollectionPointInterface::OPENING_HOURS => $openingHours['general'],
            QuoteCollectionPointInterface::SHIPPING_EXPERIENCES => $shippingExperiences,
        ]]);

        return $collectionPoint;
    }

    /**
     * Create pickup location object from platform data.
     *
     * @param Location $apiPickupLocation
     * @param OrderQualification[] $apiQualifications
     * @return QuotePickupLocationInterface
     */
    public function mapPickupLocation(
        Location $apiPickupLocation,
        array $apiQualifications
    ): QuotePickupLocationInterface {
        $apiLocation = $apiPickupLocation->getAttributes();
        $apiAddress = $apiPickupLocation->getAttributes()->getAddress();
        $distance = null; // no distance information available for click&collect locations yet
        $openingHours = $this->mapOpeningHours($apiLocation->getOpeningHours());
        $shippingExperiences = $this->mapShippingExperiences($apiQualifications);

        $pickupLocation = $this->pickupLocationFactory->create(['data' => [
            QuotePickupLocationInterface::PICKUP_LOCATION_ID => $apiPickupLocation->getId(),
            QuotePickupLocationInterface::NAME => $apiLocation->getName(),
            QuotePickupLocationInterface::COUNTRY => $apiAddress->getCountryCode(),
            QuotePickupLocationInterface::REGION => $apiAddress->getAdministrativeArea(),
            QuotePickupLocationInterface::POSTCODE => $apiAddress->getPostalCode(),
            QuotePickupLocationInterface::CITY => $apiAddress->getLocality(),
            QuotePickupLocationInterface::STREET => $apiAddress->getLines(),
            QuotePickupLocationInterface::DISTANCE => $distance,
            QuotePickupLocationInterface::OPENING_HOURS => $openingHours,
            QuotePickupLocationInterface::SHIPPING_EXPERIENCES => $shippingExperiences,
        ]]);

        return $pickupLocation;
    }
}
