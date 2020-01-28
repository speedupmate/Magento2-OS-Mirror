<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Api\Shipment;

/**
 * Shipment Status Interface.
 *
 * A shipment status represents the current progress of booking a label with
 * a carrier through the Temando platform. To avoid collisions, all Temando
 * shipment status codes are prefixed with 4006.
 *
 * @api
 * @package Temando\Shipping\Api
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
interface ShipmentStatusInterface
{
    const STATUS_PENDING = 40060100;
    const STATUS_FULFILLED = 40060200;
    const STATUS_CANCELLED = 40060300;
    const STATUS_COMPLETING = 40060400;
    const STATUS_COMPLETED = 40060450;
    const STATUS_ERROR = 40060500;

    /**
     * Obtain human readable representation of shipment status.
     *
     * @param int $statusCode
     * @return string
     */
    public function getStatusText(int $statusCode): string;

    /**
     * Obtain numeric representation of shipment status.
     *
     * @param string $status
     * @return int
     */
    public function getStatusCode(string $status): int;
}
