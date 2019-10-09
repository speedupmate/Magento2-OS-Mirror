<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order;

use Magento\Framework\DataObject;

/**
 * Temando Order Custom Attributes Entity
 *
 * @package Temando\Shipping\Model
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CustomAttributes extends DataObject implements CustomAttributesInterface
{
    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->getData(CustomAttributesInterface::STORE_CODE);
    }

    /**
     * @return string
     */
    public function getCustomerGroupCode()
    {
        return $this->getData(CustomAttributesInterface::CUSTOMER_GROUP_CODE);
    }
}
