<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Checkout\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Temando\Shipping\Api\Checkout\GuestCartCollectionPointManagementInterface;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;

/**
 * Process "collection point" delivery option (guest checkout).
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
     * @param GuestShippingAddressManagementInterface $addressManagement
     * @param CollectionPointManagement $collectionPointManagement
     */
    public function __construct(
        GuestShippingAddressManagementInterface $addressManagement,
        CollectionPointManagement $collectionPointManagement
    ) {
        $this->addressManagement = $addressManagement;
        $this->collectionPointManagement = $collectionPointManagement;
    }

    /**
     * Save a customer's search for collection points.
     *
     * @param string $cartId
     * @param string $countryId
     * @param string $postcode
     * @return CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest(
        string $cartId,
        string $countryId,
        string $postcode
    ): CollectionPointSearchRequestInterface {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'));
        }

        return $this->collectionPointManagement->saveSearchRequest($shippingAddress->getId(), $countryId, $postcode);
    }

    /**
     * Delete a customer's search for collection points.
     *
     * @param string $cartId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest(string $cartId): bool
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotDeleteException(__('Unable to load shipping address for specified quote.'));
        }

        return $this->collectionPointManagement->deleteSearchRequest($shippingAddress->getId());
    }

    /**
     * Retrieve collection points matching the customer's search parameters.
     *
     * @param string $cartId
     * @return \Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints(string $cartId): array
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            return [];
        }

        return $this->collectionPointManagement->getCollectionPoints($shippingAddress->getId());
    }

    /**
     * Select a given collection point for checkout.
     *
     * @param string $cartId
     * @param string $collectionPointId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint(string $cartId, string $collectionPointId): bool
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'));
        }

        return $this->collectionPointManagement->selectCollectionPoint($shippingAddress->getId(), $collectionPointId);
    }
}
