<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Temando\Shipping\Api\Checkout\CartCollectionPointManagementInterface as CheckoutCartCollectionPointManagement;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Api\Delivery\CartCollectionPointManagementInterface;

/**
 * Manage Collection Point Searches
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\CartCollectionPointManagement
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
     * @var CheckoutCartCollectionPointManagement
     */
    private $cartCollectionPointManagement;

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
     * @param CheckoutCartCollectionPointManagement $cartCollectionPointManagement
     * @param ShippingAddressManagementInterface $addressManagement
     * @param CollectionPointManagement $collectionPointManagement
     */
    public function __construct(
        CheckoutCartCollectionPointManagement $cartCollectionPointManagement,
        ShippingAddressManagementInterface $addressManagement,
        CollectionPointManagement $collectionPointManagement
    ) {
        $this->cartCollectionPointManagement = $cartCollectionPointManagement;
        $this->addressManagement = $addressManagement;
        $this->collectionPointManagement = $collectionPointManagement;
    }

    /**
     * @param int $cartId
     * @param string $countryId
     * @param string $postcode
     * @return CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest($cartId, $countryId, $postcode)
    {
        return $this->cartCollectionPointManagement->saveSearchRequest($cartId, $countryId, $postcode);
    }

    /**
     * @param int $cartId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest($cartId)
    {
        return $this->cartCollectionPointManagement->deleteSearchRequest($cartId);
    }

    /**
     * @param int $cartId
     * @return QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints($cartId)
    {
        return $this->cartCollectionPointManagement->getCollectionPoints($cartId);
    }

    /**
     * @param int $cartId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint($cartId, $entityId)
    {
        try {
            $shippingAddress = $this->addressManagement->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new CouldNotSaveException(__('Unable to load shipping address for specified quote.'), $exception);
        }

        return $this->collectionPointManagement->selectCollectionPoint($shippingAddress->getId(), $entityId);
    }
}
