<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request;

use Temando\Shipping\Rest\Adapter\OrderApiInterface;
use Temando\Shipping\Rest\Request\Type\OrderRequestTypeInterface;

/**
 * Temando API Order Operation Parameters
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class OrderRequest
{
    /**
     * @var OrderRequestTypeInterface
     */
    private $order;

    /**
     * @var string
     */
    private $action;

    /**
     * OrderRequest constructor.
     *
     * @param OrderRequestTypeInterface $order
     * @param string $action
     */
    public function __construct(
        OrderRequestTypeInterface $order,
        $action = OrderApiInterface::ACTION_CREATE
    ) {
        $this->order = $order;
        $this->action = $action;
    }

    /**
     * @return string[]
     */
    public function getPathParams()
    {
        // create
        if (!$this->order->getId()) {
            return [];
        }

        // update
        return [
            $this->order->getId(),
        ];
    }

    /**
     * @return string[]
     */
    public function getRequestParams()
    {
        switch ($this->action) {
            case OrderApiInterface::ACTION_ALLOCATE_PICKUP:
            case OrderApiInterface::ACTION_ALLOCATE_SHIPMENT:
                // create with shipment / pickup fulfillment
                return [
                    'action' => $this->action,
                    'experience' => $this->order->getSelectedExperienceCode()
                ];
            case OrderApiInterface::ACTION_CREATE:
                // regular create
                return ['experience' => $this->order->getSelectedExperienceCode()];
            default:
                // update
                return [];
        }
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return json_encode($this->order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
