<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Shipment;

use Temando\Shipping\Api\Shipment\ShipmentStatusInterface;

/**
 * Temando Shipment Status
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentStatus implements ShipmentStatusInterface
{
    /**
     * Obtain complete list of available shipment status codes
     *
     * @return int[]
     */
    private function getStatusMap(): array
    {
        $statusMap =  [
            'pending' => self::STATUS_PENDING,
            'fulfilled' => self::STATUS_FULFILLED,
            'cancelled' => self::STATUS_CANCELLED,
            'completing' => self::STATUS_COMPLETING,
            'completed' => self::STATUS_COMPLETED,
            'error' => self::STATUS_ERROR,
        ];

        return $statusMap;
    }

    /**
     * Obtain human readable representation of shipment status.
     *
     * @param int $statusCode
     * @return string
     */
    public function getStatusText(int $statusCode): string
    {
        $statusMap = $this->getStatusMap();
        $statusMap = array_flip($statusMap);

        if (!isset($statusMap[$statusCode])) {
            return '';
        }

        return $statusMap[$statusCode];
    }

    /**
     * Obtain numeric representation of shipment status.
     *
     * @param string $status
     * @return int
     */
    public function getStatusCode(string $status): int
    {
        $statusMap = $this->getStatusMap();

        if (!isset($statusMap[$status])) {
            return 0;
        }

        return $statusMap[$status];
    }
}
