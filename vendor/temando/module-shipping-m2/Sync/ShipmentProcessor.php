<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Sync;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface as SalesShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Temando\Shipping\Model\DocumentationInterface;
use Temando\Shipping\Model\ResourceModel\Order\OrderReference as OrderReferenceResource;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\Sales\Service\ShipmentService;
use Temando\Shipping\Model\Shipment\PackageInterface;
use Temando\Shipping\Model\Shipment\PackageItemInterface;
use Temando\Shipping\Model\Shipping\Carrier;
use Temando\Shipping\Model\StreamEventInterface;
use Temando\Shipping\Sync\Exception\EventException;
use Temando\Shipping\Sync\Exception\EventProcessorException;

/**
 * Temando Shipment Event Processor
 *
 * @package Temando\Shipping\Sync
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentProcessor implements EntityProcessorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $salesOrderRepository;

    /**
     * @var SalesShipmentRepositoryInterface
     */
    private $salesShipmentRepository;

    /**
     * @var ShipmentReferenceRepositoryInterface
     */
    private $shipmentReferenceRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrder;

    /**
     * @var OrderReferenceResource
     */
    private $orderReferenceResource;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $itemCreationFactory;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    private $trackCreationFactory;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * @var ShipmentCreationArgumentsInterfaceFactory
     */
    private $shipmentCreationArgumentsFactory;

    /**
     * @var ShipmentCreationArgumentsExtensionInterfaceFactory
     */
    private $shipmentCreationArgumentsExtensionFactory;

    /**
     * ShipmentEventProcessor constructor.
     * @param OrderRepositoryInterface $salesOrderRepository
     * @param SalesShipmentRepositoryInterface $salesShipmentRepository
     * @param ShipmentReferenceRepositoryInterface $shipmentReferenceRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipOrderInterface $shipOrder
     * @param OrderReferenceResource $orderReferenceResource
     * @param ShipmentItemCreationInterfaceFactory $itemCreationFactory
     * @param ShipmentTrackCreationInterfaceFactory $trackCreationFactory
     * @param ShipmentService $shipmentService
     * @param ShipmentCreationArgumentsInterfaceFactory $shipmentCreationArgumentsFactory
     * @param ShipmentCreationArgumentsExtensionInterfaceFactory $shipmentCreationArgumentsExtensionFactory
     */
    public function __construct(
        OrderRepositoryInterface $salesOrderRepository,
        SalesShipmentRepositoryInterface $salesShipmentRepository,
        ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        ShipOrderInterface $shipOrder,
        OrderReferenceResource $orderReferenceResource,
        ShipmentItemCreationInterfaceFactory $itemCreationFactory,
        ShipmentTrackCreationInterfaceFactory $trackCreationFactory,
        ShipmentService $shipmentService,
        ShipmentCreationArgumentsInterfaceFactory $shipmentCreationArgumentsFactory,
        ShipmentCreationArgumentsExtensionInterfaceFactory $shipmentCreationArgumentsExtensionFactory
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->salesShipmentRepository = $salesShipmentRepository;
        $this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipOrder = $shipOrder;
        $this->orderReferenceResource = $orderReferenceResource;
        $this->itemCreationFactory = $itemCreationFactory;
        $this->trackCreationFactory = $trackCreationFactory;
        $this->shipmentService = $shipmentService;
        $this->shipmentCreationArgumentsFactory = $shipmentCreationArgumentsFactory;
        $this->shipmentCreationArgumentsExtensionFactory = $shipmentCreationArgumentsExtensionFactory;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param string $sku
     * @return int|null
     */
    private function getOrderItemIdBySku(OrderInterface $salesOrder, $sku)
    {
        foreach ($salesOrder->getItems() as $item) {
            if ($item->getSku() === $sku) {
                return $item->getItemId();
            }
        }

        return null;
    }

    /**
     * Create new shipment
     *
     * @param string $extShipmentId
     * @return int Processed entity ID.
     * @throws EventException
     * @throws EventProcessorException
     */
    private function create(string $extShipmentId): int
    {
        try {
            // load external shipment
            $shipment = $this->shipmentRepository->getById($extShipmentId);
        } catch (LocalizedException $e) {
            throw EventProcessorException::processingFailed(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                $extShipmentId,
                $e
            );
        }

        // skip shipment event if no fulfillment with tracking number is available
        $fulfillment = $shipment->getFulfillment();
        if (!$fulfillment || !$fulfillment->getTrackingReference()) {
            throw EventException::operationSkipped(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                StreamEventInterface::EVENT_TYPE_CREATE,
                $extShipmentId,
                'No fulfillment information available.'
            );
        }

        // find local order id for external order
        $orderId = $this->orderReferenceResource->getOrderIdByExtOrderId($shipment->getOrderId());
        if (!$orderId) {
            throw EventProcessorException::processingFailed(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                $extShipmentId
            );
        }

        // items
        /** @var ShipmentItemCreationInterface[] $creationItems */
        $creationItems = [];
        $packages = $shipment->getPackages() ?: [];

        /** @var PackageItemInterface[] $fulfilledItems */
        $fulfilledItems = array_reduce($packages, function (array $items, PackageInterface $package) {
            $items = array_merge($items, $package->getItems());
            return $items;
        }, []);

        $salesOrder = $this->salesOrderRepository->get($orderId);

        foreach ($fulfilledItems as $fulfilledItem) {
            if (!isset($creationItems[$fulfilledItem->getSku()])) {
                // add new creation item
                $orderItemId = $this->getOrderItemIdBySku($salesOrder, $fulfilledItem->getSku());

                $creationItem = $this->itemCreationFactory->create();
                $creationItem->setQty($fulfilledItem->getQty());
                $creationItem->setOrderItemId($orderItemId);

                $creationItems[$fulfilledItem->getSku()] = $creationItem;
            } else {
                // increase qty of existing creation item
                $creationItem = $creationItems[$fulfilledItem->getSku()];
                $creationItem->setQty($fulfilledItem->getQty() + $creationItem->getQty());
            }
        }

        // tracking
        $tracking = $this->trackCreationFactory->create();
        $tracking->setCarrierCode(Carrier::CODE);
        $tracking->setTitle($fulfillment->getServiceName());
        $tracking->setTrackNumber($fulfillment->getTrackingReference());

        // shipping label
        /** @var DocumentationInterface $documentation */
        $documentation = current($shipment->getDocumentation());
        $labelUrl = empty($documentation) ? '' : $documentation->getUrl();

        $extensionAttributes = $this->shipmentCreationArgumentsExtensionFactory->create();
        $extensionAttributes->setExtLocationId($shipment->getOriginId());
        $extensionAttributes->setExtShipmentId($extShipmentId);
        $extensionAttributes->setExtTrackingReference($fulfillment->getTrackingReference());
        $extensionAttributes->setShippingLabel($labelUrl);

        $arguments = $this->shipmentCreationArgumentsFactory->create();
        $arguments->setExtensionAttributes($extensionAttributes);

        try {
            $shipmentId = $this->shipOrder->execute(
                $orderId,
                $creationItems, // items within partial shipments
                true, // Notify the customer (tracking email)
                false, // add comment
                null,
                [$tracking],
                [], // Package definition
                $arguments
            );
        } catch (LocalizedException $e) {
            throw EventProcessorException::processingFailed(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                $extShipmentId,
                $e
            );
        }

        return (int) $shipmentId;
    }

    /**
     * Update existing shipment, i.e. add tracking information, update status
     *
     * @param string $extShipmentId
     * @return int
     * @throws EventException
     * @throws EventProcessorException
     */
    private function modify(string $extShipmentId): int
    {
        try {
            $shipmentReference = $this->shipmentReferenceRepository->getByExtShipmentId($extShipmentId);
        } catch (NoSuchEntityException $e) {
            return $this->create($extShipmentId);
        }

        try {
            // read shipment from platform, trigger post-process operations
            $this->shipmentService->read($extShipmentId, (int) $shipmentReference->getShipmentId());
        } catch (NoSuchEntityException $exception) {
            // shipment does not exist at platform, skip
            throw EventException::operationSkipped(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                StreamEventInterface::EVENT_TYPE_MODIFY,
                $extShipmentId,
                $exception->getMessage()
            );
        } catch (LocalizedException $exception) {
            // processing local shipment failed, try again later
            throw EventProcessorException::processingFailed(
                StreamEventInterface::ENTITY_TYPE_SHIPMENT,
                $extShipmentId,
                $exception
            );
        }

        return (int) $shipmentReference->getShipmentId();
    }

    /**
     * @param string $operation
     * @param string $extShipmentId
     * @return int Processed entity ID.
     * @throws EventException
     * @throws EventProcessorException
     */
    public function execute(string $operation, string $extShipmentId): int
    {
        if ($operation == StreamEventInterface::EVENT_TYPE_MODIFY) {
            return $this->modify($extShipmentId);
        }

        if ($operation == StreamEventInterface::EVENT_TYPE_CREATE) {
            return $this->create($extShipmentId);
        }

        throw EventException::unknownOperation(
            StreamEventInterface::ENTITY_TYPE_SHIPMENT,
            $operation
        );
    }
}
