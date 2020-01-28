<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Model\Shipment\ShipmentErrorInterface;
use Temando\Shipping\Model\Shipment\ShipmentErrorInterfaceFactory;
use Temando\Shipping\Model\ShipmentInterface;
use Temando\Shipping\Rest\Response\DataObject\Shipment;
use Temando\Shipping\Rest\Response\Document\SaveOrderInterface;
use Temando\Shipping\Webservice\Response\Type\OrderResponseType;
use Temando\Shipping\Webservice\Response\Type\OrderResponseTypeFactory;

/**
 * Map API data to application data object
 *
 * @package  Temando\Shipping\Rest
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class OrderResponseMapper
{
    /**
     * @var OrderResponseTypeFactory
     */
    private $orderResponseFactory;

    /**
     * @var ShipmentResponseMapper
     */
    private $shipmentResponseMapper;

    /**
     * @var ShipmentErrorInterfaceFactory
     */
    private $shipmentErrorFactory;

    /**
     * OrderResponseMapper constructor.
     * @param OrderResponseTypeFactory $orderResponseFactory
     * @param ShipmentResponseMapper $shipmentResponseMapper
     * @param ShipmentErrorInterfaceFactory $shipmentErrorFactory
     */
    public function __construct(
        OrderResponseTypeFactory $orderResponseFactory,
        ShipmentResponseMapper $shipmentResponseMapper,
        ShipmentErrorInterfaceFactory $shipmentErrorFactory
    ) {
        $this->orderResponseFactory = $orderResponseFactory;
        $this->shipmentResponseMapper = $shipmentResponseMapper;
        $this->shipmentErrorFactory = $shipmentErrorFactory;
    }

    /**
     * @param Shipment[] $apiIncluded
     * @return ShipmentErrorInterface[]
     */
    private function mapErrors(array $apiIncluded)
    {
        /** @var Shipment[] $includedErrors */
        $includedErrors = array_filter($apiIncluded, function (Shipment $element) {
            return ($element->getType() == 'error');
        });

        $allocationErrors = [];
        foreach ($includedErrors as $item) {
            $allocationError = $this->shipmentErrorFactory->create(['data' => [
                ShipmentErrorInterface::TITLE => $item->getAttributes()->getTitle(),
                ShipmentErrorInterface::CODE => $item->getAttributes()->getCode(),
                ShipmentErrorInterface::STATUS => $item->getAttributes()->getStatus(),
                ShipmentErrorInterface::DETAIL => $item->getAttributes()->getDetail(),
            ]]);

            $allocationErrors[]= $allocationError;
        }

        return $allocationErrors;
    }

    /**
     * @param Shipment[] $apiIncluded
     * @return ShipmentInterface[]
     */
    private function mapShipments(array $apiIncluded)
    {
        /** @var Shipment[] $includedShipments */
        $includedShipments = array_filter($apiIncluded, function (Shipment $element) {
            return ($element->getType() == 'shipment');
        });

        $shipments = [];
        foreach ($includedShipments as $shipment) {
            $shipments[]= $this->shipmentResponseMapper->map($shipment);
        }

        return $shipments;
    }

    /**
     * @param SaveOrderInterface $apiOrder
     * @return OrderResponseType
     */
    public function map(SaveOrderInterface $apiOrder)
    {
        $orderId = $apiOrder->getData()->getId();
        $shipments = $this->mapShipments($apiOrder->getIncluded());
        $errors = $this->mapErrors($apiOrder->getIncluded());

        $orderResponse = $this->orderResponseFactory->create([
            'orderId' => $orderId,
            'errors' => $errors,
            'shipments' => $shipments,
        ]);

        return $orderResponse;
    }
}
