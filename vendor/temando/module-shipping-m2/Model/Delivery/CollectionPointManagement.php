<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Model\Checkout\Delivery\CollectionPointManagement as CheckoutCollectionPointManagement;
use Temando\Shipping\Model\ResourceModel\Repository\QuoteCollectionPointRepositoryInterface;

/**
 * Manage Collection Point Access
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\CollectionPointManagement
 *
 * @package Temando\Shipping\Model
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CollectionPointManagement
{
    /**
     * @var CheckoutCollectionPointManagement
     */
    private $collectionPointManagement;

    /**
     * @var QuoteCollectionPointRepositoryInterface
     */
    private $collectionPointRepository;

    /**
     * CollectionPointManagement constructor.
     *
     * @param CheckoutCollectionPointManagement $collectionPointManagement
     * @param QuoteCollectionPointRepositoryInterface $collectionPointRepository
     */
    public function __construct(
        CheckoutCollectionPointManagement $collectionPointManagement,
        QuoteCollectionPointRepositoryInterface $collectionPointRepository
    ) {
        $this->collectionPointManagement = $collectionPointManagement;
        $this->collectionPointRepository = $collectionPointRepository;
    }

    /**
     * Save new search parameters, delete previous search results.
     *
     * @param int $addressId
     * @param string $countryId
     * @param string $postcode
     * @param bool $pending
     * @return CollectionPointSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest($addressId, $countryId, $postcode, $pending = false)
    {
        return $this->collectionPointManagement->saveSearchRequest($addressId, $countryId, $postcode, $pending);
    }

    /**
     * Delete search parameters, delete previous search results.
     *
     * @param int $addressId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest($addressId)
    {
        return $this->collectionPointManagement->deleteSearchRequest($addressId);
    }

    /**
     * Load all collection point search results for a given shipping address id.
     *
     * Sort by pseudo field `sort_distance` that gets added to handle null values.
     *
     * @see QuoteCollectionPointRepository::getList
     * @param int $addressId
     * @return QuoteCollectionPointInterface[]
     */
    public function getCollectionPoints($addressId)
    {
        return $this->collectionPointManagement->getCollectionPoints($addressId);
    }

    /**
     * Delete all collection point search results for a given shipping address id.
     *
     * @param int $addressId
     * @return CollectionPointSearchResultInterface
     * @throws CouldNotDeleteException
     */
    public function deleteCollectionPoints($addressId)
    {
        return $this->collectionPointManagement->deleteCollectionPoints($addressId);
    }

    /**
     * Mark a collection point search result as selected for a given shipping address id.
     *
     * @param int $addressId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint($addressId, $entityId)
    {
        $collectionPoints = $this->getCollectionPoints($addressId);

        try {
            array_walk($collectionPoints, function (QuoteCollectionPointInterface $collectionPoint) use ($entityId) {
                $isSelected = ($entityId == $collectionPoint->getEntityId());
                /** @var QuoteCollectionPoint $collectionPoint */
                $collectionPoint->setData(QuoteCollectionPointInterface::SELECTED, $isSelected);
                $this->collectionPointRepository->save($collectionPoint);
            });
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__('Unable to select collection point.'), $exception);
        }

        return true;
    }
}
