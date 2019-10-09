<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Model\BatchInterface;
use Temando\Shipping\Model\BatchInterfaceFactory;
use Temando\Shipping\Model\Shipment\ShipmentErrorInterface;
use Temando\Shipping\Model\Shipment\ShipmentErrorInterfaceFactory;
use Temando\Shipping\Model\Shipment\ShipmentItemInterface;
use Temando\Shipping\Model\Shipment\ShipmentItemInterfaceFactory;
use Temando\Shipping\Model\Shipment\ShipmentSummaryInterface;
use Temando\Shipping\Model\Shipment\ShipmentSummaryInterfaceFactory;
use Temando\Shipping\Rest\Response\DataObject\Batch;
use Temando\Shipping\Rest\Response\DataObject\Shipment;
use Temando\Shipping\Rest\Response\Fields\Batch\Shipment as ShipmentReference;
use Temando\Shipping\Rest\Response\Fields\Generic\Item;
use Temando\Shipping\Rest\Response\Fields\Generic\Package;

/**
 * Map API data to application data object
 *
 * @package Temando\Shipping\Rest
 * @author  Rhodri Davies <rhodri.davies@temando.com>
 * @author  Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BatchResponseMapper
{
    /**
     * @var BatchInterfaceFactory
     */
    private $batchFactory;

    /**
     * @var ShipmentSummaryInterfaceFactory
     */
    private $shipmentFactory;

    /**
     * @var ShipmentItemInterfaceFactory
     */
    private $shipmentItemFactory;

    /**
     * @var ShipmentErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var ShippingLocationMapper
     */
    private $locationMapper;

    /**
     * BatchResponseMapper constructor.
     *
     * @param BatchInterfaceFactory $batchFactory
     * @param ShipmentSummaryInterfaceFactory $shipmentFactory
     * @param ShipmentItemInterfaceFactory $shipmentItemFactory
     * @param ShipmentErrorInterfaceFactory $errorFactory
     * @param ShippingLocationMapper $locationMapper
     */
    public function __construct(
        BatchInterfaceFactory $batchFactory,
        ShipmentSummaryInterfaceFactory $shipmentFactory,
        ShipmentItemInterfaceFactory $shipmentItemFactory,
        ShipmentErrorInterfaceFactory $errorFactory,
        ShippingLocationMapper $locationMapper
    ) {
        $this->batchFactory = $batchFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->errorFactory = $errorFactory;
        $this->locationMapper = $locationMapper;
    }

    /**
     * @param Shipment $apiShipment
     * @return ShipmentItemInterface[]
     */
    private function mapItems(Shipment $apiShipment): array
    {
        $packages = $apiShipment->getAttributes()->getPackages() ?: [];

        $apiItems = array_reduce($packages, function (array $apiItems, Package $apiPackage) {
            $apiItems = array_merge($apiItems, $apiPackage->getItems());
            return $apiItems;
        }, []);

        $apiItems = array_merge($apiItems, $apiShipment->getAttributes()->getItems());

        $shipmentItems = array_map(function (Item $apiItem) {
            return $this->shipmentItemFactory->create(['data' => [
                ShipmentItemInterface::NAME => $apiItem->getProduct()->getDescription(),
                ShipmentItemInterface::SKU => $apiItem->getProduct()->getSku(),
                ShipmentItemInterface::QTY => $apiItem->getQuantity(),
            ]]);
        }, $apiItems);

        return $shipmentItems;
    }

    /**
     * @param ShipmentReference $apiShipmentReference
     * @param Shipment $apiShipment
     *
     * @return ShipmentSummaryInterface
     */
    private function mapShipment(
        ShipmentReference $apiShipmentReference,
        Shipment $apiShipment
    ): ShipmentSummaryInterface {
        $errors = [];
        foreach ($apiShipmentReference->getErrors() as $apiError) {
            $errors[]= $this->errorFactory->create(['data' => [
                ShipmentErrorInterface::TITLE => $apiError->getTitle(),
                ShipmentErrorInterface::DETAIL => $apiError->getDetail(),
            ]]);
        }

        $order = $apiShipment->getAttributes()->getOrder();
        $items = $this->mapItems($apiShipment);
        $location = $this->locationMapper->map($apiShipment->getAttributes()->getDestination());

        $shipment = $this->shipmentFactory->create(['data' => [
            ShipmentSummaryInterface::ORDER_ID => $order ? $order->getReference() : '',
            ShipmentSummaryInterface::SHIPMENT_ID => $apiShipment->getId(),
            ShipmentSummaryInterface::STATUS => $apiShipment->getAttributes()->getStatus(),
            ShipmentSummaryInterface::ERRORS => $errors,
            ShipmentSummaryInterface::ITEMS => $items,
            ShipmentSummaryInterface::DESTINATION_LOCATION => $location,
        ]]);

        return $shipment;
    }

    /**
     * @param Batch $apiBatch
     *
     * @return BatchInterface
     */
    public function map(Batch $apiBatch): BatchInterface
    {
        $batchId = $apiBatch->getId();
        $status = $apiBatch->getAttributes()->getStatus();
        $createdAtDate = $apiBatch->getAttributes()->getCreatedAt();
        $updatedAtDate = $apiBatch->getAttributes()->getModifiedAt();
        $failedShipments = [];
        $includedShipments = [];
        $documentation = $apiBatch->getAttributes()->getDocumentation();

        $shipments = [];
        foreach ($apiBatch->getShipments() as $shipment) {
            $shipments[$shipment->getId()] = $shipment;
        }

        // split shipments into failed and successfully created
        foreach ($apiBatch->getAttributes()->getShipments() as $shipmentReference) {
            $mappedShipment = $this->mapShipment($shipmentReference, $shipments[$shipmentReference->getId()]);
            if ($shipmentReference->getStatus() === 'error') {
                $failedShipments[$shipmentReference->getId()] = $mappedShipment;
            } else {
                $includedShipments[$shipmentReference->getId()] = $mappedShipment;
            }
        }

        $batch = $this->batchFactory->create(['data' => [
            BatchInterface::BATCH_ID => (string)$batchId,
            BatchInterface::STATUS => (string)$status,
            BatchInterface::CREATED_AT_DATE => (string)$createdAtDate,
            BatchInterface::UPDATED_AT_DATE => (string)$updatedAtDate,
            BatchInterface::INCLUDED_SHIPMENTS => $includedShipments,
            BatchInterface::FAILED_SHIPMENTS => $failedShipments,
            BatchInterface::DOCUMENTATION => (string)$documentation,
        ]]);

        return $batch;
    }
}
