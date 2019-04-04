<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Shipment;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\ShipmentInterface;
use Temando\Shipping\Rest\Adapter\ShipmentApiInterface;
use Temando\Shipping\Rest\EntityMapper\ShipmentResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ItemRequestInterfaceFactory;

/**
 * Temando Shipment Repository
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentRepository implements ShipmentRepositoryInterface
{
    /**
     * @var ShipmentApiInterface
     */
    private $apiAdapter;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @var ShipmentResponseMapper
     */
    private $shipmentMapper;

    /**
     * ShipmentRepository constructor.
     * @param ShipmentApiInterface $apiAdapter
     * @param ItemRequestInterfaceFactory $requestFactory
     * @param ShipmentResponseMapper $shipmentMapper
     */
    public function __construct(
        ShipmentApiInterface $apiAdapter,
        ItemRequestInterfaceFactory $requestFactory,
        ShipmentResponseMapper $shipmentMapper
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->requestFactory = $requestFactory;
        $this->shipmentMapper = $shipmentMapper;
    }

    /**
     * Load external shipment entity from platform.
     *
     * @param string $shipmentId
     * @return ShipmentInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getById(string $shipmentId): ShipmentInterface
    {
        if (!$shipmentId) {
            throw new LocalizedException(__('An error occurred while loading data.'));
        }

        try {
            $request = $this->requestFactory->create(['entityId' => $shipmentId]);
            $apiShipment = $this->apiAdapter->getShipment($request);
            $shipment = $this->shipmentMapper->map($apiShipment);
        } catch (AdapterException $e) {
            if ($e->getCode() === 404) {
                throw NoSuchEntityException::singleField('shipmentId', $shipmentId);
            }

            throw new LocalizedException(__('An error occurred while loading data.'), $e);
        }

        return $shipment;
    }

    /**
     * Cancel external shipment at the platform.
     *
     * @param string $shipmentId
     * @return ShipmentInterface
     * @throws CouldNotDeleteException
     */
    public function cancel(string $shipmentId): ShipmentInterface
    {
        try {
            $request = $this->requestFactory->create(['entityId' => $shipmentId]);
            $apiShipment = $this->apiAdapter->cancelShipment($request);
            $shipment = $this->shipmentMapper->map($apiShipment);
        } catch (AdapterException $e) {
            throw new CouldNotDeleteException(__('Unable to cancel shipment: %1.', $e->getMessage()), $e);
        }

        return $shipment;
    }
}
