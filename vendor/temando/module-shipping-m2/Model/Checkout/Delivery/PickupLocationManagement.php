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
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchRequestInterfaceFactory;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\Delivery\QuotePickupLocation;
use Temando\Shipping\Model\ResourceModel\Repository\PickupLocationSearchRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Repository\QuotePickupLocationRepositoryInterface;

/**
 * Manage Pickup Location Access
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupLocationManagement
{
    /**
     * @var PickupLocationSearchRepositoryInterface
     */
    private $searchRequestRepository;

    /**
     * @var PickupLocationSearchRequestInterfaceFactory
     */
    private $searchRequestFactory;

    /**
     * @var QuotePickupLocationRepositoryInterface
     */
    private $pickupLocationRepository;

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
     * PickupLocationManagement constructor.
     *
     * @param PickupLocationSearchRepositoryInterface $searchRequestRepository
     * @param PickupLocationSearchRequestInterfaceFactory $searchRequestFactory
     * @param QuotePickupLocationRepositoryInterface $pickupLocationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilderFactory
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        PickupLocationSearchRepositoryInterface $searchRequestRepository,
        PickupLocationSearchRequestInterfaceFactory $searchRequestFactory,
        QuotePickupLocationRepositoryInterface $pickupLocationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchRequestRepository = $searchRequestRepository;
        $this->searchRequestFactory = $searchRequestFactory;
        $this->pickupLocationRepository = $pickupLocationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Save new search parameters, delete previous search results.
     *
     * @param int $addressId
     * @param bool $active
     * @return PickupLocationSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest(int $addressId, bool $active): PickupLocationSearchRequestInterface
    {
        $searchRequest = $this->searchRequestFactory->create(['data' => [
            PickupLocationSearchRequestInterface::SHIPPING_ADDRESS_ID => $addressId,
            PickupLocationSearchRequestInterface::ACTIVE => $active,
        ]]);

        try {
            $this->searchRequestRepository->save($searchRequest);
            $this->deletePickupLocations($addressId);
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
            $this->deletePickupLocations($addressId);
        } catch (LocalizedException $exception) {
            throw new CouldNotDeleteException(__('Unable to delete search parameters.'), $exception);
        }

        return true;
    }

    /**
     * Load all collection location results for a given shipping address id.
     *
     * Sort by pseudo field `sort_distance` that gets added to handle null values.
     *
     * @see QuotePickupLocationRepository::getList
     * @param int $addressId
     * @return QuotePickupLocationInterface[]
     */
    public function getPickupLocations(int $addressId): array
    {
        $filter = $this->filterBuilder
            ->setField(QuotePickupLocationInterface::RECIPIENT_ADDRESS_ID)
            ->setValue($addressId)
            ->setConditionType('eq')
            ->create();

        $distanceSortOrder = $this->sortOrderBuilder
            ->setField('sort_distance')
            ->setAscendingDirection()
            ->create();
        $nameSortOrder = $this->sortOrderBuilder
            ->setField(QuotePickupLocationInterface::NAME)
            ->setAscendingDirection()
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $this->searchCriteriaBuilder->setSortOrders([$distanceSortOrder, $nameSortOrder]);
        $criteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->pickupLocationRepository->getList($criteria);
        return $searchResult->getItems();
    }

    /**
     * Delete all collect location search results for a given shipping address id.
     *
     * @param int $addressId
     * @return PickupLocationSearchResultInterface
     * @throws CouldNotDeleteException
     */
    public function deletePickupLocations(int $addressId): PickupLocationSearchResultInterface
    {
        $filter = $this->filterBuilder
            ->setField(QuotePickupLocationInterface::RECIPIENT_ADDRESS_ID)
            ->setValue($addressId)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $criteria = $this->searchCriteriaBuilder->create();

        try {
            $searchResult = $this->pickupLocationRepository->getList($criteria);
            $pickupLocations = $searchResult->getItems();
            array_walk($pickupLocations, function (QuotePickupLocationInterface $pickupLocation) {
                $this->pickupLocationRepository->delete($pickupLocation);
            });
        } catch (LocalizedException $exception) {
            throw new CouldNotDeleteException(__('Unable to delete collect locations.'), $exception);
        }

        return $searchResult;
    }

    /**
     * Mark a pickup location search result as selected for a given shipping address id.
     *
     * @param int $addressId
     * @param string $pickupLocationId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation(int $addressId, string $pickupLocationId): bool
    {
        $pickupLocations = $this->getPickupLocations($addressId);

        try {
            $updateSelection = function (QuotePickupLocationInterface $pickupLocation) use ($pickupLocationId) {
                $isSelected = ($pickupLocationId == $pickupLocation->getPickupLocationId());
                /** @var QuotePickupLocation $pickupLocation */
                $pickupLocation->setData(QuotePickupLocationInterface::SELECTED, $isSelected);
                $this->pickupLocationRepository->save($pickupLocation);
            };

            array_walk($pickupLocations, $updateSelection);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__('Unable to select pickup location.'), $exception);
        }

        return true;
    }
}
