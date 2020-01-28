<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Data\Delivery;

/**
 * Temando Quote Pickup Location Interface – Checkout/Quoting
 *
 * @api
 * @package Temando\Shipping\Api
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface QuotePickupLocationInterface
{
    const ENTITY_ID = 'entity_id';
    const PICKUP_LOCATION_ID = 'pickup_location_id';
    const RECIPIENT_ADDRESS_ID = 'recipient_address_id';
    const NAME = 'name';
    const COUNTRY = 'country';
    const REGION = 'region';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const STREET = 'street';
    const DISTANCE = 'distance';
    const OPENING_HOURS = 'opening_hours';
    const SHIPPING_EXPERIENCES = 'shipping_experiences';
    const SELECTED = 'selected';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getPickupLocationId();

    /**
     * @return int
     */
    public function getRecipientAddressId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string[]
     */
    public function getStreet();

    /**
     * @return int
     */
    public function getDistance();

    /**
     * @return string[][]
     */
    public function getOpeningHours();

    /**
     * @return string[][]
     */
    public function getShippingExperiences();

    /**
     * @return bool
     */
    public function isSelected();
}
