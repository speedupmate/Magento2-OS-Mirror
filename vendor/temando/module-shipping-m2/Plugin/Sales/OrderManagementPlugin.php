<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Plugin\Sales;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Api\Shipment\ShipmentStatusInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface;
use Temando\Shipping\Model\Sales\Service\ShipmentService;

/**
 * OrderManagementPlugin
 *
 * @package Temando\Shipping\Plugin
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderManagementPlugin
{
    /**
     * @var ShipmentReferenceRepositoryInterface
     */
    private $shipmentReferenceRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderManagementPlugin constructor.
     * @param ShipmentReferenceRepositoryInterface $shipmentReferenceRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ShipmentService $shipmentService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ShipmentService $shipmentService,
        LoggerInterface $logger
    ) {
        $this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentService = $shipmentService;
        $this->logger = $logger;
    }

    /**
     * Attempt to cancel shipments after an order is cancelled.
     *
     * @param OrderManagementInterface $orderService
     * @param bool $result
     * @param int $orderId
     * @return bool
     */
    public function afterCancel(OrderManagementInterface $orderService, bool $result, int $orderId): bool
    {
        if (!$result) {
            // order was not cancelled, nothing to do
            return $result;
        }

        $notCancelledFilter = $this->filterBuilder
            ->setField(ShipmentInterface::SHIPMENT_STATUS)
            ->setValue(ShipmentStatusInterface::STATUS_CANCELLED)
            ->setConditionType('neq')
            ->create();
        $statusUnknownFilter = $this->filterBuilder
            ->setField(ShipmentInterface::SHIPMENT_STATUS)
            ->setConditionType('null')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ShipmentInterface::ORDER_ID, $orderId)
            ->addFilters([$notCancelledFilter, $statusUnknownFilter])
            ->create();

        /** @var ShipmentReferenceInterface[] $shipmentReferences */
        $shipmentReferences = $this->shipmentReferenceRepository->getList($searchCriteria);
        foreach ($shipmentReferences as $shipmentReference) {
            try {
                $this->shipmentService->cancel(
                    $shipmentReference->getExtShipmentId(),
                    $shipmentReference->getShipmentId()
                );
            } catch (LocalizedException $exception) {
                $message = sprintf(
                    'Shipment %d (%s) could not be cancelled.',
                    $shipmentReference->getShipmentId(),
                    $shipmentReference->getExtShipmentId()
                );
                $this->logger->critical($message, ['exception' => $exception]);
            }
        }

        return $result;
    }
}
