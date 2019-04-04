<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Checkout\Delivery;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchRequestInterfaceFactory;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Model\Delivery\QuoteCollectionPoint;
use Temando\Shipping\Model\ResourceModel\Repository\CollectionPointSearchRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Repository\QuoteCollectionPointRepositoryInterface;

/**
 * Manage Collection Point Access
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
     * @var CollectionPointSearchRepositoryInterface
     */
    private $searchRequestRepository;

    /**
     * @var CollectionPointSearchRequestInterfaceFactory
     */
    private $searchRequestFactory;

    /**
     * @var QuoteCollectionPointRepositoryInterface
     */
    private $collectionPointRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * CollectionPointManagement constructor.
     *
     * @param CollectionPointSearchRepositoryInterface $searchRequestRepository
     * @param CollectionPointSearchRequestInterfaceFactory $searchRequestFactory
     * @param QuoteCollectionPointRepositoryInterface $collectionPointRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        CollectionPointSearchRepositoryInterface $searchRequestRepository,
        CollectionPointSearchRequestInterfaceFactory $searchRequestFactory,
        QuoteCollectionPointRepositoryInterface $collectionPointRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchRequestRepository = $searchRequestRepository;
        $this->searchRequestFactory = $searchRequestFactory;
        $this->collectionPointRepository = $collectionPointRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
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
    public function saveSearchRequest(
        int $addressId,
        string $countryId,
        string $postcode,
        bool $pending = false
    ): CollectionPointSearchRequestInterface {
        $data = [
            CollectionPointSearchRequestInterface::SHIPPING_ADDRESS_ID => $addressId,
            CollectionPointSearchRequestInterface::PENDING => $pending,
        ];

        if ($countryId && $postcode) {
            $data[CollectionPointSearchRequestInterface::COUNTRY_ID] = $countryId;
            $data[CollectionPointSearchRequestInterface::POSTCODE] = $postcode;
        }

        $searchRequest = $this->searchRequestFactory->create(['data' => $data]);

        try {
            $this->searchRequestRepository->save($searchRequest);
            $this->deleteCollectionPoints($addressId);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__('Unable to save search parameters.'), $exception);
        }

        return $searchRequest;
    }

    /**
     * Delete search parameters, delete previous search results.
     *
     * @param int $addressId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteSearchRequest(int $addressId): bool
    {
        try {
            $this->searchRequestRepository->delete($addressId);
            $this->deleteCollectionPoints($addressId);
        } catch (LocalizedException $exception) {
            throw new CouldNotDeleteException(__('Unable to delete search parameters.'), $exception);
        }

        return true;
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
    public function getCollectionPoints(int $addressId): array
    {
        $filter = $this->filterBuilder
            ->setField(QuoteCollectionPointInterface::RECIPIENT_ADDRESS_ID)
            ->setValue($addressId)
            ->setConditionType('eq')
            ->create();

        $distanceSortOrder = $this->sortOrderBuilder
            ->setField('sort_distance')
            ->setAscendingDirection()
            ->create();
        $nameSortOrder = $this->sortOrderBuilder
            ->setField(QuoteCollectionPointInterface::NAME)
            ->setAscendingDirection()
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $this->searchCriteriaBuilder->setSortOrders([$distanceSortOrder, $nameSortOrder]);
        $criteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->collectionPointRepository->getList($criteria);
        return $searchResult->getItems();
    }

    /**
     * Delete all collection point search results for a given shipping address id.
     *
     * @param int $addressId
     * @return CollectionPointSearchResultInterface
     * @throws CouldNotDeleteException
     */
    public function deleteCollectionPoints(int $addressId): CollectionPointSearchResultInterface
    {
        $filter = $this->filterBuilder
            ->setField(QuoteCollectionPointInterface::RECIPIENT_ADDRESS_ID)
            ->setValue($addressId)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $criteria = $this->searchCriteriaBuilder->create();

        try {
            $searchResult = $this->collectionPointRepository->getList($criteria);
            $collectionPoints = $searchResult->getItems();
            array_walk($collectionPoints, function (QuoteCollectionPointInterface $collectionPoint) {
                $this->collectionPointRepository->delete($collectionPoint);
            });
        } catch (LocalizedException $exception) {
            throw new CouldNotDeleteException(__('Unable to delete collection points.'), $exception);
        }

        return $searchResult;
    }

    /**
     * Mark a collection point search result as selected for a given shipping address id.
     *
     * @param int $addressId
     * @param string $collectionPointId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint(int $addressId, string $collectionPointId): bool
    {
        $collectionPoints = $this->getCollectionPoints($addressId);

        try {
            $updateSelection = function (QuoteCollectionPointInterface $collectionPoint) use ($collectionPointId) {
                $isSelected = ($collectionPointId == $collectionPoint->getCollectionPointId());
                /** @var QuoteCollectionPoint $collectionPoint */
                $collectionPoint->setData(QuoteCollectionPointInterface::SELECTED, $isSelected);
                $this->collectionPointRepository->save($collectionPoint);
            };

            array_walk($collectionPoints, $updateSelection);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__('Unable to select collection point.'), $exception);
        }

        return true;
    }
}
