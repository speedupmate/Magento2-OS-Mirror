<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Delivery;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchRequestInterface;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\Checkout\Delivery\PickupLocationManagement as CheckoutPickupLocationManagement;
use Temando\Shipping\Model\ResourceModel\Repository\QuotePickupLocationRepositoryInterface;

/**
 * Manage Pickup Location Access
 *
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Model\Checkout\Delivery\PickupLocationManagement
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupLocationManagement
{
    /**
     * @var CheckoutPickupLocationManagement
     */
    private $pickupLocationManagement;

    /**
     * @var QuotePickupLocationRepositoryInterface
     */
    private $pickupLocationRepository;

    /**
     * PickupLocationManagement constructor.
     *
     * @param CheckoutPickupLocationManagement $pickupLocationManagement
     * @param QuotePickupLocationRepositoryInterface $pickupLocationRepository
     */
    public function __construct(
        CheckoutPickupLocationManagement $pickupLocationManagement,
        QuotePickupLocationRepositoryInterface $pickupLocationRepository
    ) {
        $this->pickupLocationManagement = $pickupLocationManagement;
        $this->pickupLocationRepository = $pickupLocationRepository;
    }

    /**
     * Save new search parameters, delete previous search results.
     *
     * @param int $addressId
     * @param bool $active
     * @return PickupLocationSearchRequestInterface
     * @throws CouldNotSaveException
     */
    public function saveSearchRequest($addressId, $active)
    {
        return $this->pickupLocationManagement->saveSearchRequest($addressId, $active);
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
        return $this->pickupLocationManagement->deleteSearchRequest($addressId);
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
    public function getPickupLocations($addressId)
    {
        return $this->pickupLocationManagement->getPickupLocations($addressId);
    }

    /**
     * Delete all collect location search results for a given shipping address id.
     *
     * @param int $addressId
     * @return PickupLocationSearchResultInterface
     * @throws CouldNotDeleteException
     */
    public function deletePickupLocations($addressId)
    {
        return $this->pickupLocationManagement->deletePickupLocations($addressId);
    }

    /**
     * Mark a pickup location search result as selected for a given shipping address id.
     *
     * @param int $addressId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation($addressId, $entityId)
    {
        $pickupLocations = $this->getPickupLocations($addressId);

        try {
            array_walk($pickupLocations, function (QuotePickupLocationInterface $pickupLocation) use ($entityId) {
                $isSelected = ($entityId == $pickupLocation->getEntityId());
                /** @var QuotePickupLocation $pickupLocation */

                $pickupLocation->setData(QuotePickupLocationInterface::SELECTED, $isSelected);
                $this->pickupLocationRepository->save($pickupLocation);
            });
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(__('Unable to select pickup location.'), $exception);
        }

        return true;
    }
}
