<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Delivery\GuestCartCollectionPointManagementInterface;
use Temando\Shipping\Api\Checkout\GuestCartCollectionPointManagementInterface as CheckoutGuestCartCollectionPointManagement;

/**
 * Manage Collection Point Searches
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\GuestCartCollectionPointManagement
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class GuestCartCollectionPointManagement implements GuestCartCollectionPointManagementInterface
{
    /**
     * @var CheckoutGuestCartCollectionPointManagement
     */
    private $guestCartCollectionPointManagement;

    /**
     * @var GuestShippingAddressManagementInterface
     */
    private $addressManagement;

    /**
     * @var CollectionPointManagement
     */
    private $collectionPointManagement;

    /**
     * GuestCartCollectionPointManagement constructor.
     *
     * @param CheckoutGuestCartCollectionPointManagement $guestCartCollectionPointManagement
     * @param GuestShippingAddressManagementInterface $addressManagement
     * @param CollectionPointManagement $collectionPointManagement
     */
    public function __construct(
        CheckoutGuestCartCollectionPointManagement $guestCartCollectionPointManagement,
        GuestShippingAddressManagementInterface $addressManagement,
        CollectionPointManagement $collectionPointManagement
    ) {
        $this->guestCartCollectionPointManagement = $guestCartCollectionPointManagement;
        $this->addressManagement = $addressManagement;
        $this->collectionPointManagement = $collectionPointManagement;
    }

    /**
     * @param string $cartId
     * @param string $countryId
     * @param string $postcode
     * @return CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest($cartId, $countryId, $postcode)
    {
        return $this->guestCartCollectionPointManagement->saveSearchRequest($cartId, $countryId, $postcode);
    }

    /**
     * @param string $cartId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest($cartId)
    {
        return $this->guestCartCollectionPointManagement->deleteSearchRequest($cartId);
    }

    /**
     * @param string $cartId
     * @return \Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints($cartId)
    {
        return $this->guestCartCollectionPointManagement->getCollectionPoints($cartId);
    }

    /**
     * @param string $cartId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint($cartId, $entityId)
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'));
        }

        return $this->collectionPointManagement->selectCollectionPoint($shippingAddress->getId(), $entityId);
    }
}
