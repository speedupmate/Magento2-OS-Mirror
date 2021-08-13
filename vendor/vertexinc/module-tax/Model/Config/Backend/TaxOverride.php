<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Vertex\Tax\Model\Config\DeliveryTerm;

/**
 * Config model for Tax Override
 */
class TaxOverride extends Value
{
    /** @var DeliveryTerm */
    private $deliveryTermConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param DeliveryTerm $deliveryTermConfig
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        DeliveryTerm $deliveryTermConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->deliveryTermConfig = $deliveryTermConfig;
    }

    /**
     * Unserialize the value loaded from the database
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $value = $this->deliveryTermConfig->makeArrayFieldValue($this->getValue());
        $this->setValue($value);
        return $this;
    }

    /**
     * Serialize the value before it is saved to the database
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->deliveryTermConfig->makeStorableArrayFieldValue($this->getValue());
        $this->setValue($value);
        return $this;
    }
}
