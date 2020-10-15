<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Vertex\Tax\Model\ResourceModel\CustomerCountry\CollectionFactory;
use Vertex\Tax\Model\ResourceModel\CustomerCountry\Collection;

/**
 * Performs Datastore-related actions for the CustomerCountry repository
 */
class CustomerCountry extends AbstractDb
{
    /** @var string */
    const TABLE = 'vertex_customer_country';

    /** @var string */
    const FIELD_ID = 'customer_id';

    /** @var string */
    const FIELD_CODE = 'customer_country';

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param string|null $connectionName
     */
    public function __construct(Context $context, CollectionFactory $collectionFactory, $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     *
     * MEQP2 Warning: Protected method. Needed to override AbstractDb's _construct
     */
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init(static::TABLE, static::FIELD_ID);
    }

    /**
     * Retrieve a list of Customer Countries indexed by Customer ID
     *
     * @param int[] $customerIds
     * @return CustomerCountry[]
     */
    public function getArrayByCustomerIds($customerIds): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::FIELD_ID, ['in' => $customerIds]);
        $collection->load();

        $result = [];
        foreach ($collection->getItems() as $item) {
            /** @var CustomerCountry $item */
            $result[$item->getCustomerId()] = $item;
        }

        return $result;
    }
}
