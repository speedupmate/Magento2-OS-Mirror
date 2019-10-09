<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\Adapter;

use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\OrderRequest;
use Temando\Shipping\Rest\Request\UpdateRequestInterface;
use Temando\Shipping\Rest\Response\Document\SaveOrderInterface;
use Temando\Shipping\Rest\Response\Document\UpdateOrderInterface;

/**
 * The Temando Order API interface defines the supported subset of operations
 * as available at the Temando API.
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
interface OrderApiInterface
{
    const ACTION_CREATE = 'create';
    const ACTION_ALLOCATE_PICKUP = 'allocate';
    const ACTION_ALLOCATE_SHIPMENT = 'allocate';
    const ACTION_UPDATE = 'update';

    /**
     * Create order.
     *
     * @param OrderRequest $request
     * @return SaveOrderInterface
     * @throws AdapterException
     */
    public function createOrder(OrderRequest $request);

    /**
     * Replace entire order.
     *
     * @param OrderRequest $request
     * @return SaveOrderInterface
     * @throws AdapterException
     */
    public function updateOrder(OrderRequest $request);

    /**
     * Update specific order attributes.
     *
     * @param UpdateRequestInterface $request
     * @return UpdateOrderInterface
     * @throws AdapterException
     */
    public function patchOrder(UpdateRequestInterface $request);
}
