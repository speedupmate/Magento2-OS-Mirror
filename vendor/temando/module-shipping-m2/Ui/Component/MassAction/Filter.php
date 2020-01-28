<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\Component\MassAction;

use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Temando\Shipping\Model\PackagingInterface;
use Temando\Shipping\Model\PickupInterface;
use Temando\Shipping\Model\ResourceModel\Pickup\Grid\Collection;
use Temando\Shipping\Model\ResourceModel\Repository\PackagingRepositoryInterface;

/**
 * Temando Mass Action ID Filter
 *
 * @package Temando\Shipping\Ui
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Filter
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * Filter constructor.
     *
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory)
    {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Obtain the list of selected containers:
     * - inclusive:
     * -- some items: non-empty `$selected` array
     * - exclusive:
     * -- all items: empty `$selected` array, empty `$excluded` array
     * -- some items: empty `$selected` array, non-empty `$excluded` array
     *
     * @param PackagingRepositoryInterface $packagingRepository
     * @param string[] $selected
     * @param string[] $excluded
     * @return string[]
     */
    public function getPackagingIds(PackagingRepositoryInterface $packagingRepository, $selected, $excluded)
    {
        if (!empty($selected)) {
            return $selected;
        }

        // read all ids from repo
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $containers = $packagingRepository->getList($searchCriteriaBuilder->create());
        $selected = array_map(function (PackagingInterface $container) {
            return $container->getPackagingId();
        }, $containers);
        // remove $excluded from ids
        $selected = array_diff($selected, $excluded);

        return $selected;
    }

    /**
     * Obtain the list of selected pickups:
     * - inclusive:
     * -- some items: non-empty `$selected` array
     * - exclusive:
     * -- all items: empty `$selected` array, empty `$excluded` array
     * -- some items: empty `$selected` array, non-empty `$excluded` array
     *
     * @param Collection $collection
     * @param string[] $selected
     * @param string[] $excluded
     * @return string[]
     */
    public function getPickupIds(Collection $collection, $selected, $excluded)
    {
        if (!empty($selected)) {
            return $selected;
        }

        // read all ids from collection
        $pickups = $collection->getItems();
        $selected = array_map(function (PickupInterface $pickup) {
            return $pickup->getPickupId();
        }, $pickups);
        // remove $excluded from ids
        $selected = array_diff($selected, $excluded);

        return $selected;
    }
}
