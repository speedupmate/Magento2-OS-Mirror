<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Order;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Temando\Shipping\Api\Data\Order\OrderReferenceInterface;
use Temando\Shipping\Api\Data\Order\OrderReferenceInterfaceFactory;
use Temando\Shipping\Model\OrderInterface;
use Temando\Shipping\Model\ResourceModel\Repository\OrderRepositoryInterface;
use Temando\Shipping\Rest\Adapter\OrderApiInterface;
use Temando\Shipping\Rest\EntityMapper\OrderRequestTypeBuilder;
use Temando\Shipping\Rest\EntityMapper\OrderResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\OrderRequestFactory;
use Temando\Shipping\Rest\Request\Type\OrderRequestTypeInterface;
use Temando\Shipping\Webservice\Response\Type\OrderResponseType;

/**
 * Temando Order Repository
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderApiInterface
     */
    private $apiAdapter;

    /**
     * @var OrderRequestFactory
     */
    private $requestFactory;

    /**
     * @var OrderRequestTypeBuilder
     */
    private $requestBuilder;

    /**
     * @var OrderResponseMapper
     */
    private $orderResponseMapper;

    /**
     * @var OrderReference
     */
    private $resource;

    /**
     * @var OrderReferenceInterfaceFactory
     */
    private $orderReferenceFactory;

    /**
     * OrderRepository constructor.
     * @param OrderApiInterface $apiAdapter
     * @param OrderRequestFactory $requestFactory
     * @param OrderRequestTypeBuilder $requestBuilder
     * @param OrderResponseMapper $orderResponseMapper
     * @param OrderReference $resource
     * @param OrderReferenceInterfaceFactory $orderReferenceFactory
     */
    public function __construct(
        OrderApiInterface $apiAdapter,
        OrderRequestFactory $requestFactory,
        OrderRequestTypeBuilder $requestBuilder,
        OrderResponseMapper $orderResponseMapper,
        OrderReference $resource,
        OrderReferenceInterfaceFactory $orderReferenceFactory
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->requestFactory = $requestFactory;
        $this->requestBuilder = $requestBuilder;
        $this->orderResponseMapper = $orderResponseMapper;
        $this->resource = $resource;
        $this->orderReferenceFactory = $orderReferenceFactory;
    }

    /**
     * @param int $entityId
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    private function getReferenceById($entityId)
    {
        /** @var \Temando\Shipping\Model\Order\OrderReference $orderReference */
        $orderReference = $this->orderReferenceFactory->create();
        $this->resource->load($orderReference, $entityId);

        if (!$orderReference->getId()) {
            throw new NoSuchEntityException(__('Order with id "%1" does not exist.', $entityId));
        }

        return $orderReference;
    }

    /**
     * Create a regular order at the platform.
     *
     * @param OrderRequestTypeInterface $orderType
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    private function create(OrderRequestTypeInterface $orderType)
    {
        $orderRequest = $this->requestFactory->create([
            'order' => $orderType,
            'action' => OrderApiInterface::ACTION_CREATE,
        ]);

        try {
            $createdOrder = $this->apiAdapter->createOrder($orderRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to save order.'), $e);
        }

        return $this->orderResponseMapper->map($createdOrder);
    }

    /**
     * Create a regular order at the platform and allocate pickup fulfillments.
     *
     * @param OrderRequestTypeInterface $orderType
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    private function allocatePickup(OrderRequestTypeInterface $orderType)
    {
        $orderRequest = $this->requestFactory->create([
            'order' => $orderType,
            'action' => OrderApiInterface::ACTION_ALLOCATE_PICKUP,
        ]);

        try {
            $createdOrder = $this->apiAdapter->createOrder($orderRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to save order.'), $e);
        }

        return $this->orderResponseMapper->map($createdOrder);
    }

    /**
     * Create a regular order at the platform and allocate shipments.
     *
     * @param OrderRequestTypeInterface $orderType
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    private function allocateShipment(OrderRequestTypeInterface $orderType)
    {
        $orderRequest = $this->requestFactory->create([
            'order' => $orderType,
            'action' => OrderApiInterface::ACTION_ALLOCATE_SHIPMENT,
        ]);

        try {
            $allocatedOrder = $this->apiAdapter->createOrder($orderRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to allocate shipments.'), $e);
        }

        return $this->orderResponseMapper->map($allocatedOrder);
    }

    /**
     * Update a previously created order at the platform.
     *
     * @param OrderRequestTypeInterface $orderType
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    private function update(OrderRequestTypeInterface $orderType)
    {
        $orderRequest = $this->requestFactory->create([
            'order' => $orderType,
            'action' => OrderApiInterface::ACTION_UPDATE,
        ]);

        try {
            $updatedOrder = $this->apiAdapter->updateOrder($orderRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to save order.'), $e);
        }

        return $this->orderResponseMapper->map($updatedOrder);
    }

    /**
     * @param OrderInterface $order
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    public function save(OrderInterface $order)
    {
        // build order request type
        $orderType = $this->requestBuilder->build($order);

        // may be replaced by config setting in the future.
        $isOrderAllocationEnabled = true;
        $isPaymentPending = ($order->getStatus() === OrderInterface::STATUS_AWAITING_PAYMENT);
        $isUpdate = $order->getOrderId() && $order->getSourceId();

        $pickup = $order->getPickupLocation();
        $isPickupOrder = !empty($pickup) && !empty($pickup->getPickupLocationId());

        if ($isUpdate) {
            $orderResponse = $this->update($orderType);
        } elseif ($isPickupOrder) {
            $orderResponse = $this->allocatePickup($orderType);
        } elseif ($isOrderAllocationEnabled && !$isPaymentPending) {
            $orderResponse = $this->allocateShipment($orderType);
        } else {
            $orderResponse = $this->create($orderType);
        }

        if ($order->getSourceId() && !$order->getOrderId()) {
            // persist order reference if
            // - local order entity exists
            // - remote order entity does not yet exist
            $orderReference = $this->orderReferenceFactory->create(['data' => [
                OrderReferenceInterface::EXT_ORDER_ID => $orderResponse->getOrderId(),
                OrderReferenceInterface::ORDER_ID => $order->getSourceId(),
            ]]);

            $this->saveReference($orderReference);
        }

        return $orderResponse;
    }

    /**
     * @param OrderReferenceInterface $orderReference
     * @return OrderReferenceInterface
     * @throws CouldNotSaveException
     */
    public function saveReference(OrderReferenceInterface $orderReference)
    {
        try {
            /** @var \Temando\Shipping\Model\Order\OrderReference $orderReference */
            $this->resource->save($orderReference);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to save order reference.'), $exception);
        }

        return $orderReference;
    }

    /**
     * @param string $orderId Temando Order ID
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getReferenceByExtOrderId($orderId)
    {
        $entityId = $this->resource->getIdByExtOrderId($orderId);
        if (!$entityId) {
            throw new NoSuchEntityException(__('Order reference to "%1" does not exist.', $orderId));
        }

        return $this->getReferenceById($entityId);
    }

    /**
     * @param int $orderId
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getReferenceByOrderId($orderId)
    {
        $entityId = $this->resource->getIdByOrderId($orderId);
        if (!$entityId) {
            $msg = 'Order reference for order "%1" does not exist.';
            throw new NoSuchEntityException(__($msg, $orderId));
        }

        return $this->getReferenceById($entityId);
    }
}
