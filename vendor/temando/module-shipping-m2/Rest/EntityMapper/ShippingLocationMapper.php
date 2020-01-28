<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Rest\Response\Fields\LocationAttributes;
use Temando\Shipping\Model\Shipment\LocationInterface;
use Temando\Shipping\Model\Shipment\LocationInterfaceFactory;

/**
 * Map API data to application data object
 *
 * The shipping location mapper is responsible for transforming shipping
 * addresses which are obtained from the platform as members of a primary
 * entity, e.g. a Shipment or Batch.
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.temando.com/
 */
class ShippingLocationMapper
{
    /**
     * @var LocationInterfaceFactory
     */
    private $locationFactory;

    /**
     * ShippingLocationMapper constructor.
     *
     * @param LocationInterfaceFactory $locationFactory
     */
    public function __construct(LocationInterfaceFactory $locationFactory)
    {
        $this->locationFactory = $locationFactory;
    }

    /**
     * Map location attributes to a shipment location.
     *
     * @param LocationAttributes|null $apiLocation
     * @return LocationInterface|null
     */
    public function map(?LocationAttributes $apiLocation): ?LocationInterface
    {
        if (!$apiLocation) {
            return null;
        }

        $contact = $apiLocation->getContact();
        $location = $this->locationFactory->create(['data' => [
            LocationInterface::NAME => '',
            LocationInterface::COMPANY => $contact ? $contact->getOrganisationName() : '',
            LocationInterface::PERSON_FIRST_NAME => $contact ? $contact->getPersonFirstName() : '',
            LocationInterface::PERSON_LAST_NAME => $contact ? $contact->getPersonLastName() : '',
            LocationInterface::EMAIL => $contact ? $contact->getEmail() : '',
            LocationInterface::PHONE_NUMBER => $contact ? $contact->getPhoneNumber() : '',
            LocationInterface::STREET => $apiLocation->getAddress()->getLines(),
            LocationInterface::CITY => $apiLocation->getAddress()->getLocality(),
            LocationInterface::POSTAL_CODE => $apiLocation->getAddress()->getPostalCode(),
            LocationInterface::REGION_CODE => $apiLocation->getAddress()->getAdministrativeArea(),
            LocationInterface::COUNTRY_CODE => $apiLocation->getAddress()->getCountryCode(),
            LocationInterface::TYPE => $apiLocation->getType(),
        ]]);

        return $location;
    }
}
