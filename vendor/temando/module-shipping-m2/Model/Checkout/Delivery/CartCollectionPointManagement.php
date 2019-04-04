<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Checkout\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Checkout\CartCollectionPointManagementInterface;

/**
 * Process "collection point" delivery option.
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CartCollectionPointManagement implements CartCollectionPointManagementInterface
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $addressManagement;

    /**
     * @var CollectionPointManagement
     */
    private $collectionPointManagement;

    /**
     * CartCollectionPointManagement constructor.
     *
     * @param ShippingAddressManagementInterface $addressManagement
     * @param CollectionPointManagement $collectionPointManagement
     */
    public function __construct(
        ShippingAddressManagementInterface $addressManagement,
        CollectionPointManagement $collectionPointManagement
    ) {
        $this->addressManagement = $addressManagement;
        $this->collectionPointManagement = $collectionPointManagement;
    }

    /**
     * Save a customer's search for collection points.
     *
     * @param int $cartId
     * @param string $countryId
     * @param string $postcode
     * @return CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest(
        int $cartId,
        string $countryId,
        string $postcode
    ): CollectionPointSearchRequestInterface {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'), $exception);
        }

        return $this->collectionPointManagement->saveSearchRequest($shippingAddress->getId(), $countryId, $postcode);
    }

    /**
     * Delete a customer's search for collection points.
     *
     * @param int $cartId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest(int $cartId): bool
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotDeleteException(__('Unable to load shipping address for specified quote.'), $exception);
        }

        return $this->collectionPointManagement->deleteSearchRequest($shippingAddress->getId());
    }

    /**
     * Retrieve collection points matching the customer's search parameters.
     *
     * @param int $cartId
     * @return QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints(int $cartId): array
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
     * @param int $cartId
     * @param string $collectionPointId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint(int $cartId, string $collectionPointId): bool
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'), $exception);
        }

        return $this->collectionPointManagement->selectCollectionPoint($shippingAddress->getId(), $collectionPointId);
    }
}
