<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Sales\Service;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\ShipmentInterface;

/**
 * Temando Shipment Service.
 *
 * The shipment service allows to execute additional operations after
 * performing CRUD operations at the platform.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentService
{
    const OPERATION_CREATE = 'create';
    const OPERATION_READ = 'read';
    const OPERATION_UPDATE = 'update';
    const OPERATION_CANCEL = 'cancel';

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentOperationPool
     */
    private $operationPool;

    /**
     * ShipmentService constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentOperationPool $operationPool
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentOperationPool $operationPool
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->operationPool = $operationPool;
    }

    /**
     * Cancel shipment at the platform, execute additional operations on local shipment entity.
     *
     * @param string $shipmentId
     * @param int $salesShipmentId
     * @return ShipmentInterface
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function cancel(string $shipmentId, int $salesShipmentId): ShipmentInterface
    {
        $shipment = $this->shipmentRepository->cancel($shipmentId);

        $cancelOperations = $this->operationPool->get(self::OPERATION_CANCEL);
        foreach ($cancelOperations as $operation) {
            $operation->execute($shipment, $salesShipmentId);
        }

        return $shipment;
    }

    /**
     * Read shipment form the platform, execute additional operations on local shipment entity.
     *
     * @param string $shipmentId
     * @param int $salesShipmentId
     * @return ShipmentInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function read(string $shipmentId, int $salesShipmentId): ShipmentInterface
    {
        $shipment = $this->shipmentRepository->getById($shipmentId);

        $readOperations = $this->operationPool->get(self::OPERATION_READ);
        foreach ($readOperations as $operation) {
            $operation->execute($shipment, $salesShipmentId);
        }

        return $shipment;
    }
}
