<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Checkout\Delivery;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Api\Checkout\CartPickupLocationManagementInterface;

/**
 * Process "pickup location" delivery option.
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <benjamin.heuer@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CartPickupLocationManagement implements CartPickupLocationManagementInterface
{
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
     * @param ShippingAddressManagementInterface $addressManagement
     * @param PickupLocationManagement $pickupLocationManagement
     */
    public function __construct(
        ShippingAddressManagementInterface $addressManagement,
        PickupLocationManagement $pickupLocationManagement
    ) {
        $this->addressManagement = $addressManagement;
        $this->pickupLocationManagement = $pickupLocationManagement;
    }

    /**
     * Retrieve pickup locations matching the customer's search parameters.
     *
     * @param int $cartId
     * @return QuotePickupLocationInterface[]
     */
    public function getPickupLocations(int $cartId): array
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            return [];
        }

        return $this->pickupLocationManagement->getPickupLocations($shippingAddress->getId());
    }

    /**
     * Select a given pickup location for checkout.
     *
     * @param int $cartId
     * @param string $pickupLocationId
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function selectPickupLocation(int $cartId, string $pickupLocationId): bool
    {
        $shippingAddress = $this->addressManagement->get($cartId);

        return $this->pickupLocationManagement->selectPickupLocation($shippingAddress->getId(), $pickupLocationId);
    }
}
