<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Checkout;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;

/**
 * Process "collection point" delivery option.
 *
 * @api
 * @package Temando\Shipping\Api
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface CartCollectionPointManagementInterface
{
    /**
     * Save a customer's search for collection points.
     *
     * @param int $cartId
     * @param string $countryId
     * @param string $postcode
     * @return \Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest(
        int $cartId,
        string $countryId,
        string $postcode
    ): CollectionPointSearchRequestInterface;

    /**
     * Delete a customer's search for collection points.
     *
     * @param int $cartId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest(int $cartId): bool;

    /**
     * Retrieve collection points matching the customer's search parameters.
     *
     * @param int $cartId
     * @return \Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints(int $cartId): array;

    /**
     * Select a given collection point for checkout.
     *
     * @param int $cartId
     * @param string $collectionPointId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint(int $cartId, string $collectionPointId): bool;
}
