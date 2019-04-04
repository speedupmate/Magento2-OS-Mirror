<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Webservice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Temando API Resource Collection
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
abstract class Collection extends DataCollection
{
    /**
     * @var string
     */
    protected $_itemObjectClass = Document::class;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;

        parent::__construct($entityFactory);
    }

    /**
     * Perform API call
     * @return \Magento\Framework\DataObject[]
     */
    abstract public function fetchData();

    /**
     * Sort documents
     * @return void
     */
    private function sortItems()
    {
        foreach ($this->_orders as $field => $direction) {
            if ($direction === self::SORT_ORDER_ASC) {
                uasort($this->_items, function (Document $itemA, Document $itemB) use ($field, $direction) {
                    return $itemA->getDataByKey($field) > $itemB->getDataByKey($field) ? 1 : -1;
                });
            } else {
                uasort($this->_items, function (Document $itemA, Document $itemB) use ($field, $direction) {
                    return $itemA->getDataByKey($field) < $itemB->getDataByKey($field) ? 1 : -1;
                });
            }
        }
    }

    /**
     * Paginate documents
     * @return void
     */
    private function sliceItems()
    {
        $offset = $this->_pageSize * ($this->_curPage -1);
        $limit = $this->_pageSize;

        $this->_items = array_slice($this->_items, $offset, $limit);
    }

    /**
     * Load data from repository/api and convert to Document class
     *
     * @see \Magento\Framework\Data\Collection\AbstractDb::loadWithFilter()
     * @see \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider::searchResultToOutput()
     * @see \Magento\Framework\Api\Search\SearchResultInterface::getItems()
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        try {
            // load list from webservice
            $data = $this->fetchData();

            // shift response items to document class
            foreach ($data as $apiItem) {
                $item = $this->getNewEmptyItem();
                $item->addData($apiItem->getData());
                $this->addItem($item);
            }

            $this->_totalRecords = count($this->_items);
            $this->sortItems();
            $this->sliceItems();
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, 'An error occurred while requesting API listing.');
        }

        $this->_setIsLoaded();
        return $this;
    }
}
