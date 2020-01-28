<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Process "pickup location" delivery option (guest checkout).
 *
 * @api
 * @package Temando\Shipping\Api
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface GuestCartPickupLocationManagementInterface
{
    /**
     * Retrieve pickup locations matching the customer's search parameters.
     *
     * @param string $cartId
     * @return \Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface[]
     */
    public function getPickupLocations(string $cartId): array;

    /**
     * Select a given pickup location for checkout.
     *
     * @param string $cartId
     * @param string $pickupLocationId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation(string $cartId, string $pickupLocationId): bool;
}
