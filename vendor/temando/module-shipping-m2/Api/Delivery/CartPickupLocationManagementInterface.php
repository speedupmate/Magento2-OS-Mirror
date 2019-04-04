<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Delivery;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 * @deprecated since 1.5.1
 * @see \Temando\Shipping\Api\Checkout\CartPickupLocationManagementInterface
 *
 * @package Temando\Shipping\Api
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface CartPickupLocationManagementInterface
{
    /**
     * @param int $cartId
     * @return \Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface[]
     */
    public function getPickupLocations($cartId);

    /**
     * @param int $cartId
     * @param int $entityId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectPickupLocation($cartId, $entityId);
}
