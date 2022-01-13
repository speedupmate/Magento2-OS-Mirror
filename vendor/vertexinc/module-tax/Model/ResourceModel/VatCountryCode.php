<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Vertex\Tax\Model\ResourceModel\VatCountryCode\CollectionFactory;
use Vertex\Tax\Model\ResourceModel\VatCountryCode\Collection;

/**
 * Performs Datastore-related actions for the VatCountryCode repository
 */
class VatCountryCode extends AbstractDb
{
    /** @var string */
    const TABLE = 'vertex_vat_country_code';

    /** @var string */
    const FIELD_ID = 'address_id';

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
     * Retrieve a list of Vat Countries indexed by Address ID
     *
     * @param int[] $addressIds
     * @return VatCountryCode[]
     */
    public function getArrayByAddressId($addressIds): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::FIELD_ID, ['in' => $addressIds]);
        $collection->load();

        $result = [];
        foreach ($collection->getItems() as $item) {
            /** @var VatCountryCode $item */
            $result[$item->getAddressId()] = $item;
        }

        return $result;
    }
}
