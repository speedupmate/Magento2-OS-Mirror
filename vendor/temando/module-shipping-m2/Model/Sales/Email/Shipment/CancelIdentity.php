<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Sales\Email\Shipment;

use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;

/**
 * Temando Shipment Canceled Identity.
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <cnathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CancelIdentity extends ShipmentIdentity
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_CANCEL_GUEST_TEMPLATE = 'sales_email/shipment/cancel_guest_template';
    const XML_PATH_EMAIL_CANCEL_TEMPLATE = 'sales_email/shipment/cancel_template';

    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_CANCEL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_CANCEL_TEMPLATE, $this->getStore()->getStoreId());
    }
}
