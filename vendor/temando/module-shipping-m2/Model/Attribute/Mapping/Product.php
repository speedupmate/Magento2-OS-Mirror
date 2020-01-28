<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Attribute\Mapping;

use Magento\Framework\Model\AbstractModel;
use Temando\Shipping\Model\ResourceModel\Attribute\Mapping\Product as ProductResource;

/**
 * Temando Product Attribute Mapping
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Product extends AbstractModel implements ProductInterface
{
    const NODE_PATH_PREFIX = 'product';
    const NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX = 'customAttributes';

    /**
     * Init resource model.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ProductResource::class);
    }

    /**
     * @return string
     */
    public function getNodePathId()
    {
        return $this->getData(ProductInterface::NODE_PATH_ID);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getData(ProductInterface::LABEL);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(ProductInterface::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getMappingAttributeId()
    {
        return $this->getData(ProductInterface::MAPPED_ATTRIBUTE_ID);
    }

    /**
     * @return int
     */
    public function getIsDefault()
    {
        return $this->getData(ProductInterface::IS_DEFAULT);
    }
}
