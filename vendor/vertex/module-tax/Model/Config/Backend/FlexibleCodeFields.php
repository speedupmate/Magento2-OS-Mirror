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
use Vertex\Tax\Model\Config\FlexibleFieldSerializer;

/**
 * Config model for Flexible Fields configuration storage
 */
class FlexibleCodeFields extends Value
{
    /** @var FlexibleFieldSerializer */
    private $flexibleFieldSerializer;

    /** @var FlexibleFieldUtilities */
    private $utilities;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param FlexibleFieldSerializer $flexibleFieldSerializer
     * @param FlexibleFieldUtilities $utilities
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        FlexibleFieldSerializer $flexibleFieldSerializer,
        FlexibleFieldUtilities $utilities,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->flexibleFieldSerializer = $flexibleFieldSerializer;
        $this->utilities = $utilities;
    }

    /**
     * Serialize the value before it is saved to the database
     *
     * @return FlexibleCodeFields
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $this->utilities->removeEmpty($value);
            $value = $this->flexibleFieldSerializer->serialize(array_values($value));
        }
        $this->setValue($value);
        return parent::beforeSave();
    }

    /**
     * Unserialize the value loaded from the database
     *
     * @return FlexibleCodeFields
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $value = $this->flexibleFieldSerializer->unserialize($this->getValue()) ?? [];
        $result = $this->utilities->assembleValues($value, 25);
        $this->setValue($result);
        return $this;
    }
}
