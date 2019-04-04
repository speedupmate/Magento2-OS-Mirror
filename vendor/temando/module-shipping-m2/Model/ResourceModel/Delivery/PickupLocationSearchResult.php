<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Delivery;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Temando\Shipping\Api\Data\Delivery\PickupLocationSearchResultInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\Delivery\QuotePickupLocation;
use Temando\Shipping\Model\ResourceModel\Delivery\QuotePickupLocation as QuotePickupLocationResource;

/**
 * Pickup location collection
 *
 * @package Temando\Shipping\Model
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupLocationSearchResult extends AbstractCollection implements PickupLocationSearchResultInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'temando_pickup_location_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'pickup_location_collection';

    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuotePickupLocation::class, QuotePickupLocationResource::class);
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Not applicable, Collection vs. Search Result seems to be work in progress.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param QuotePickupLocationInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * Unserialize opening_hours in each item
     *
     * @return AbstractCollection
     */
    protected function _afterLoad()
    {
        /** @var QuotePickupLocation $item */
        foreach ($this->_items as $item) {
            if (is_string($item->getOpeningHours())) {
                $this->getResource()->unserializeFields($item);
            }

            // as of v1.3.0 the serialized data structure changed
            $openingHours = $item->getOpeningHours();
            if (is_array($openingHours) && !array_key_exists('general', $openingHours)) {
                $openingHours['general'] = $openingHours;
                $openingHours['specific'] = [];
                $item->setData(QuotePickupLocationInterface::OPENING_HOURS, $openingHours);
            }

            $item->setData(QuotePickupLocationInterface::SELECTED, (bool) $item->isSelected());
        }

        parent::_afterLoad();
        return $this;
    }
}
