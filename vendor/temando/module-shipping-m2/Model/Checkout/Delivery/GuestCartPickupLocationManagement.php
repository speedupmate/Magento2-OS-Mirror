<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Checkout\Delivery;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Checkout\GuestCartPickupLocationManagementInterface;

/**
 * Manage Pickup Location Searches
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class GuestCartPickupLocationManagement implements GuestCartPickupLocationManagementInterface
{
    /**
     * @var GuestShippingAddressManagementInterface
     */
    private $addressManagement;

    /**
     * @var PickupLocationManagement
     */
    private $pickupLocationManagement;

    /**
     * GuestCartPickupLocationManagement constructor.
     *
     * @param GuestShippingAddressManagementInterface $addressManagement
     * @param PickupLocationManagement $pickupLocationManagement
     */
    public function __construct(
        GuestShippingAddressManagementInterface $addressManagement,
        PickupLocationManagement $pickupLocationManagement
    ) {
        $this->addressManagement = $addressManagement;
        $this->pickupLocationManagement = $pickupLocationManagement;
    }

    /**
     * Retrieve pickup locations matching the customer's search parameters.
     *
     * @param string $cartId
     * @return QuotePickupLocationInterface[]
     * @throws NoSuchEntityException
     */
    public function getPickupLocations(string $cartId): array
    {
        $shippingAddress = $this->addressManagement->get($cartId);

        return $this->pickupLocationManagement->getPickupLocations($shippingAddress->getId());
    }

    /**
     * Select a given pickup location for checkout.
     *
     * @param string $cartId
     * @param string $pickupLocationId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation(string $cartId, string $pickupLocationId): bool
    {
        $shippingAddress = $this->addressManagement->get($cartId);

        return $this->pickupLocationManagement->selectPickupLocation($shippingAddress->getId(), $pickupLocationId);
    }
}
