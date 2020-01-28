<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Delivery;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\ResourceModel\Repository\QuotePickupLocationRepositoryInterface;

/**
 * Pickup location mass actions.
 *
 * Deleting and updating items in a batch is expensive. This class offers a more low-level approach.
 * Beware: the downside to this performance gain is that before/after actions will not be executed.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupLocationMassAction
{
    /**
     * @var QuotePickupLocation
     */
    private $resource;

    /**
     * @var QuotePickupLocationRepositoryInterface
     */
    private $repository;

    /**
     * PickupLocationMassAction constructor.
     *
     * @param QuotePickupLocation $resource
     * @param QuotePickupLocationRepositoryInterface $repository
     */
    public function __construct(QuotePickupLocation $resource, QuotePickupLocationRepositoryInterface $repository)
    {
        $this->resource = $resource;
        $this->repository = $repository;
    }

    /**
     * Delete pickup locations.
     *
     * @param int $addressId
     * @param SearchCriteriaInterface $criteria
     * @return PickupLocationSearchResultInterface
     * @throws CouldNotDeleteException
     */
    public function deletePickupLocations(int $addressId, SearchCriteriaInterface $criteria): PickupLocationSearchResultInterface
    {
        /** @var PickupLocationSearchResult $collection */
        $collection = $this->repository->getList($criteria);

        try {
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->resource->getMainTable(),
                ['recipient_address_id = (?)' => $addressId]
            );
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Unable to delete pickup locations.'), $exception);
        }

        return $collection;
    }

    /**
     * Select a pickup location, deselect all others.
     *
     * @param int $addressId
     * @param string $pickupLocationId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation(int $addressId, string $pickupLocationId): bool
    {
        try {
            $connection = $this->resource->getConnection();
            $condition = $connection->quoteInto('pickup_location_id = ?', $pickupLocationId);
            $connection->update(
                $this->resource->getMainTable(),
                [QuotePickupLocationInterface::SELECTED => new Expression("IF($condition, 1, 0)")],
                [QuotePickupLocationInterface::RECIPIENT_ADDRESS_ID => $addressId]
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to select pickup location.'), $exception);
        }

        return true;
    }
}
