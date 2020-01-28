<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Pickup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Temando\Shipping\Model\Pickup\Email\Sender\PickupSender;
use Temando\Shipping\Model\Pickup\PickupLoader;
use Temando\Shipping\Model\PickupInterface;
use Temando\Shipping\Model\PickupProviderInterface;
use Temando\Shipping\Model\ResourceModel\Repository\PickupRepositoryInterface;

/**
 * Mark Pickup Collected Action
 *
 * @package Temando\Shipping\Controller
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Collected extends Action
{
    const ADMIN_RESOURCE = 'Temando_Shipping::pickups';

    /**
     * @var PickupLoader
     */
    private $pickupLoader;

    /**
     * @var PickupProviderInterface
     */
    private $pickupProvider;

    /**
     * @var PickupRepositoryInterface
     */
    private $pickupRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PickupSender
     */
    private $pickupSender;

    /**
     * Collected constructor.
     * @param Context $context
     * @param PickupLoader $pickupLoader
     * @param PickupProviderInterface $pickupProvider
     * @param PickupRepositoryInterface $pickupRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PickupSender $pickupSender
     */
    public function __construct(
        Context $context,
        PickupLoader $pickupLoader,
        PickupProviderInterface $pickupProvider,
        PickupRepositoryInterface $pickupRepository,
        OrderRepositoryInterface $orderRepository,
        PickupSender $pickupSender
    ) {
        $this->pickupLoader = $pickupLoader;
        $this->pickupProvider = $pickupProvider;
        $this->pickupRepository = $pickupRepository;
        $this->orderRepository = $orderRepository;
        $this->pickupSender = $pickupSender;

        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('sales_order_id', 0);
        $pickupId = $this->getRequest()->getParam('pickup_id', '');

        $pickups = $this->pickupLoader->load($orderId, $pickupId);
        $this->pickupLoader->register($pickups, $orderId, $pickupId);

        /** @var \Temando\Shipping\Model\Pickup $pickup */
        $pickup = $this->pickupProvider->getPickup();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->pickupProvider->getOrder();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        try {
            $pickup->setData(PickupInterface::STATE, PickupInterface::STATE_PICKED_UP);
            $this->pickupRepository->save($pickup);
            $this->messageManager->addSuccessMessage('Pickup was collected.');
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage('Failed to update pickup status.');
            return $resultRedirect;
        }

        try {
            $this->pickupSender->setPickupCollected();
            $this->pickupSender->send($order);
            $this->messageManager->addSuccessMessage('Email confirmation was sent.');
        } catch (\Exception $exception) {
            $this->messageManager->addSuccessMessage('Email confirmation could not be sent.');
        }

        // update item quantities and order status
        $pickupItems = $pickup->getItems();
        foreach ($order->getAllItems() as $orderItem) {
            $forceIncrement = ($orderItem->isDummy(true) && ($orderItem->getProductType() === Type::TYPE_BUNDLE));

            // there is one (known) reason to increment a dummy's quantity: the bundle container, shipped separately.
            if (!isset($pickupItems[$orderItem->getSku()]) && !$forceIncrement) {
                // item is not to be collected, do not update quantity
                continue;
            }

            if ($forceIncrement) {
                // bundle parent (container) item, shipped separately, actual quantities set in child items
                $orderItem->setQtyShipped(1);
                continue;
            }

            if (!$orderItem->isDummy(true)) {
                // regular item
                $qtyShipped = $orderItem->getQtyShipped();
                $qtyFulfilled = $pickupItems[$orderItem->getSku()];
                $orderItem->setQtyShipped($qtyShipped + $qtyFulfilled);
            }
        }

        $this->orderRepository->save($order);

        return $resultRedirect;
    }
}
