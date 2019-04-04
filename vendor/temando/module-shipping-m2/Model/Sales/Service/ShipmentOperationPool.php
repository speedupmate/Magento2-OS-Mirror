<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service;

use Temando\Shipping\Model\Sales\Service\Operation\ShipmentOperationInterface;

/**
 * Temando Shipment Operation Pool.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentOperationPool
{
    /**
     * @var ShipmentOperationInterface[][]
     */
    private $operations = [];

    /**
     * ShipmentOperationPool constructor.
     * @param Operation\ShipmentOperationInterface[][] $operations
     */
    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    /**
     * @param string $operationCode
     * @return ShipmentOperationInterface[]
     */
    public function get($operationCode): array
    {
        if (!isset($this->operations[$operationCode])) {
            return [];
        }

        return $this->operations[$operationCode];
    }
}
