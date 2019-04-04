<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Temando\Shipping\Api\Checkout\CartPickupLocationManagementInterface as CheckoutCartPickupLocationManagement;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Delivery\CartPickupLocationManagementInterface;

/**
 * Manage Pickup Location Searches
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\CartPickupLocationManagement
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CartPickupLocationManagement implements CartPickupLocationManagementInterface
{
    /**
     * @var CheckoutCartPickupLocationManagement
     */
    private $cartPickupLocationManagement;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $addressManagement;

    /**
     * @var PickupLocationManagement
     */
    private $pickupLocationManagement;

    /**
     * CartPickupLocationManagement constructor.
     *
     * @param CheckoutCartPickupLocationManagement $cartPickupLocationManagement
     * @param ShippingAddressManagementInterface $addressManagement
     * @param PickupLocationManagement $pickupLocationManagement
     */
    public function __construct(
        CheckoutCartPickupLocationManagement $cartPickupLocationManagement,
        ShippingAddressManagementInterface $addressManagement,
        PickupLocationManagement $pickupLocationManagement
    ) {
        $this->cartPickupLocationManagement = $cartPickupLocationManagement;
        $this->addressManagement = $addressManagement;
        $this->pickupLocationManagement = $pickupLocationManagement;
    }

    /**
     * @param int $cartId
     * @return QuotePickupLocationInterface[]
     */
    public function getPickupLocations($cartId)
    {
        return $this->cartPickupLocationManagement->getPickupLocations($cartId);
    }

    /**
     * @param int $cartId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function selectPickupLocation($cartId, $entityId)
    {
        $shippingAddress = $this->addressManagement->get($cartId);

        return $this->pickupLocationManagement->selectPickupLocation($shippingAddress->getId(), $entityId);
    }
}
