<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order;

/**
 * Temando Order Custom Attributes Interface
 *
 * @package Temando\Shipping\Model
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface CustomAttributesInterface
{
    const STORE_CODE = 'store_code';
    const CUSTOMER_GROUP_CODE = 'customer_group_code';

    /**
     * @return string
     */
    public function getStoreCode();

    /**
     * @return string
     */
    public function getCustomerGroupCode();
}
