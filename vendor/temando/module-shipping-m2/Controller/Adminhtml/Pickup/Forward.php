<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Pickup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Temando\Shipping\Model\Pickup\PickupLoader;
use Temando\Shipping\Model\PickupInterface;
use Temando\Shipping\Model\ResourceModel\Order\OrderRepository;
use Temando\Shipping\ViewModel\DataProvider\PickupUrl;

/**
 * Temando Pickup Forward Action
 *
 * Redirect to pickup details view based on given request parameters
 *
 * @package Temando\Shipping\Controller
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Forward extends Action
{
    const ADMIN_RESOURCE = 'Temando_Shipping::pickups';

    /**
     * @var PickupLoader
     */
    private $pickupLoader;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var PickupUrl
     */
    private $pickupUrl;

    /**
     * Forward constructor.
     *
     * @param Context $context
     * @param PickupLoader $pickupLoader
     * @param OrderRepository $orderRepository
     * @param PickupUrl $pickupUrl
     */
    public function __construct(
        Context $context,
        PickupLoader $pickupLoader,
        OrderRepository $orderRepository,
        PickupUrl $pickupUrl
    ) {
        $this->pickupLoader = $pickupLoader;
        $this->orderRepository = $orderRepository;
        $this->pickupUrl = $pickupUrl;

        parent::__construct($context);
    }

    /**
     * Redirect to the Pickup view page
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        $forwardId = $this->getRequest()->getParam('pickup_id', '');
        $pickupId = preg_filter('/^PID([0-9]*)/', '$1', $forwardId);

        $pickups = $this->pickupLoader->load(0, (int) $pickupId);
        if (!isset($pickups[$pickupId])) {
            // redirect to pickups listing if pickup does not exist at the platform.
            $this->messageManager->addErrorMessage(__('Pickup "%1" does not exist.', $pickupId));
            $redirect->setPath('*/*/');
            return $redirect;
        }

        try {
            $pickup = $pickups[$pickupId];
            $order = $this->orderRepository->getReferenceByExtOrderId($pickup->getOrderId());
        } catch (NoSuchEntityException $e) {
            // redirect to pickups listing if associated order does not exist locally.
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirect->setPath('*/*/');
            return $redirect;
        }

        $urlParams =  [
            PickupInterface::PICKUP_ID => $pickupId,
            PickupInterface::SALES_ORDER_ID => $order->getOrderId(),
        ];

        if ($pickup->getState() === PickupInterface::STATE_REQUESTED) {
            $url = $this->pickupUrl->getEditActionUrl($urlParams);
        } else {
            $url = $this->pickupUrl->getViewActionUrl($urlParams);
        }

        $redirect->setUrl($url);
        return $redirect;
    }
}
