<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Meta;

/**
 * Temando API Shipment Document Meta Information
 *
 * @package Temando\Shipping\Rest
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentMeta
{
    /**
     * @var bool
     */
    private $isCancelable;

    /**
     * @return bool
     */
    public function getIsCancelable()
    {
        return $this->isCancelable;
    }

    /**
     * @param bool $isCancelable
     * @return void
     */
    public function setIsCancelable($isCancelable)
    {
        $this->isCancelable = $isCancelable;
    }
}
