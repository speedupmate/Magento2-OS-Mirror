<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Delivery\GuestCartPickupLocationManagementInterface;
use Temando\Shipping\Api\Checkout\GuestCartPickupLocationManagementInterface as CheckoutGuestCartPickupLocationManagement;

/**
 * Manage Pickup Location Searches
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\GuestCartPickupLocationManagement
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class GuestCartPickupLocationManagement implements GuestCartPickupLocationManagementInterface
{
    /**
     * @var CheckoutGuestCartPickupLocationManagement
     */
    private $guestCartPickupLocationManagement;

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
     * @param CheckoutGuestCartPickupLocationManagement $guestCartPickupLocationManagement
     * @param GuestShippingAddressManagementInterface $addressManagement
     * @param PickupLocationManagement $pickupLocationManagement
     */
    public function __construct(
        CheckoutGuestCartPickupLocationManagement $guestCartPickupLocationManagement,
        GuestShippingAddressManagementInterface $addressManagement,
        PickupLocationManagement $pickupLocationManagement
    ) {
        $this->guestCartPickupLocationManagement = $guestCartPickupLocationManagement;
        $this->addressManagement = $addressManagement;
        $this->pickupLocationManagement = $pickupLocationManagement;
    }

    /**
     * @param string $cartId
     * @return QuotePickupLocationInterface[]
     * @throws NoSuchEntityException
     */
    public function getPickupLocations($cartId)
    {
        return $this->guestCartPickupLocationManagement->getPickupLocations($cartId);
    }

    /**
     * @param string $cartId
     * @param int $entityId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation($cartId, $entityId)
    {
        $shippingAddress = $this->addressManagement->get($cartId);

        return $this->pickupLocationManagement->selectPickupLocation($shippingAddress->getId(), $entityId);
    }
}
