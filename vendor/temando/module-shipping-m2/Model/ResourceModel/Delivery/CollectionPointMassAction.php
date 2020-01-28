<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Delivery;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Temando\Shipping\Api\Data\Delivery\CollectionPointSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Model\ResourceModel\Repository\QuoteCollectionPointRepositoryInterface;

/**
 * Collection point mass actions.
 *
 * Deleting and updating items in a batch is expensive. This class offers a more low-level approach.
 * Beware: the downside to this performance gain is that before/after actions will be not executed.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CollectionPointMassAction
{
    /**
     * @var QuoteCollectionPoint
     */
    private $resource;

    /**
     * @var QuoteCollectionPointRepositoryInterface
     */
    private $repository;

    /**
     * CollectionPointMassAction constructor.
     *
     * @param QuoteCollectionPoint $resource
     * @param QuoteCollectionPointRepositoryInterface $repository
     */
    public function __construct(QuoteCollectionPoint $resource, QuoteCollectionPointRepositoryInterface $repository)
    {
        $this->resource = $resource;
        $this->repository = $repository;
    }

    /**
     * Delete collection points.
     *
     * @param int $addressId
     * @param SearchCriteriaInterface $criteria
     * @throws CouldNotDeleteException
     * @return CollectionPointSearchResultInterface
     */
    public function deleteCollectionPoints(int $addressId, SearchCriteriaInterface $criteria): CollectionPointSearchResultInterface
    {
        /** @var CollectionPointSearchResult $collection */
        $collection = $this->repository->getList($criteria);

        try {
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->resource->getMainTable(),
                ['recipient_address_id = (?)' => $addressId]
            );
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Unable to delete collection points.'), $exception);
        }

        return $collection;
    }

    /**
     * Select a collection point, deselect all others.
     *
     * @param int $addressId
     * @param string $collectionPointId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCollectionPoint(int $addressId, string $collectionPointId): bool
    {
        try {
            $connection = $this->resource->getConnection();
            $condition = $connection->quoteInto('collection_point_id = ?', $collectionPointId);
            $connection->update(
                $this->resource->getMainTable(),
                [QuoteCollectionPointInterface::SELECTED => new Expression("IF($condition, 1, 0)")],
                [QuoteCollectionPointInterface::RECIPIENT_ADDRESS_ID => $addressId]
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to select collection point.'), $exception);
        }

        return true;
    }
}
