<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\Component\Listing\Column\ShipmentStatus;

use Magento\Framework\Data\OptionSourceInterface;
use Temando\Shipping\Api\Shipment\ShipmentStatusInterface;

/**
 * Temando Shipment Status Option Source
 *
 * @package Temando\Shipping\Ui
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Options implements OptionSourceInterface
{
    /**
     * @var ShipmentStatusInterface
     */
    private $shipmentStatus;

    /**
     * Options constructor.
     * @param ShipmentStatusInterface $shipmentStatus
     */
    public function __construct(ShipmentStatusInterface $shipmentStatus)
    {
        $this->shipmentStatus = $shipmentStatus;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $displayCodes = [ShipmentStatusInterface::STATUS_CANCELLED];

        $options = array_map(function (int $statusCode) {
            return [
                'value' => $statusCode,
                'label' => __(ucfirst($this->shipmentStatus->getStatusText($statusCode))),
            ];
        }, $displayCodes);

        return $options;
    }
}
