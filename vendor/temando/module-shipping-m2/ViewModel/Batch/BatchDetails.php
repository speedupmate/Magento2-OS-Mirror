<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\Batch;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Temando\Shipping\Model\BatchInterface;
use Temando\Shipping\Model\BatchProviderInterface;
use Temando\Shipping\Model\Location\OrderAddressFactory;
use Temando\Shipping\Model\Shipment\LocationInterface;
use Temando\Shipping\Model\Shipment\ShipmentItemInterface;
use Temando\Shipping\Model\Shipment\ShipmentSummaryInterface;
use Temando\Shipping\ViewModel\DataProvider\BatchUrl;
use Temando\Shipping\ViewModel\DataProvider\OrderAddress as AddressRenderer;
use Temando\Shipping\ViewModel\DataProvider\OrderDate;

/**
 * View model for batch details page.
 *
 * @package Temando\Shipping\ViewModel
 * @author  Rhodri Davies <rhodri.davies@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BatchDetails implements ArgumentInterface
{
    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var OrderDate
     */
    private $orderDate;

    /**
     * @var BatchUrl
     */
    private $batchUrl;

    /**
     * @var OrderAddressFactory
     */
    private $addressFactory;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * BatchDetails constructor.
     *
     * @param BatchProviderInterface $batchProvider
     * @param OrderDate $orderDate
     * @param BatchUrl $batchUrl
     * @param OrderAddressFactory $addressFactory
     * @param AddressRenderer $addressRenderer
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        BatchProviderInterface $batchProvider,
        OrderDate $orderDate,
        BatchUrl $batchUrl,
        OrderAddressFactory $addressFactory,
        AddressRenderer $addressRenderer,
        UrlInterface $urlBuilder
    ) {
        $this->batchProvider = $batchProvider;
        $this->orderDate = $orderDate;
        $this->batchUrl = $batchUrl;
        $this->addressFactory = $addressFactory;
        $this->addressRenderer = $addressRenderer;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Obtain the current batch entity.
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getBatch()
    {
        /** @var \Temando\Shipping\Model\Batch $batch */
        $batch = $this->batchProvider->getBatch();
        return $batch;
    }

    /**
     * Obtain order date.
     *
     * @param string $date
     * @return \DateTime
     */
    public function getDate(string $date): \DateTime
    {
        return $this->orderDate->getDate($date);
    }

    /**
     * Obtain batch create url
     *
     * @return string
     */
    public function getNewActionUrl(): string
    {
        return $this->batchUrl->getNewActionUrl();
    }

    /**
     * Obtain batch listing url
     *
     * @return string
     */
    public function getListActionUrl(): string
    {
        return $this->batchUrl->getListActionUrl();
    }

    /**
     * Obtain url for troubleshooting failed batches
     *
     * @return string
     */
    public function getSolveUrl(): string
    {
        return $this->batchUrl->getSolveActionUrl([
            BatchInterface::BATCH_ID => $this->batchProvider->getBatch()->getBatchId(),
        ]);
    }

    /**
     * Get the order view action URL by platform shipment ID.
     *
     * @param string $extShipmentId
     * @return string
     */
    public function getOrderViewUrl($extShipmentId): string
    {
        return $this->urlBuilder->getUrl('temando/order/view', ['shipment_id' => $extShipmentId]);
    }

    /**
     * Render the recipient address.
     *
     * @param DataObject|LocationInterface $location
     * @return string
     */
    public function getShipToAddressHtml(DataObject $location): string
    {
        /** @var LocationInterface $location */
        $shippingAddress = $this->addressFactory->createFromShipmentLocation($location);
        return $this->addressRenderer->getFormattedAddress($shippingAddress);
    }

    /**
     * Obtain the items shipped with the given platform shipment id.
     *
     * @param $extShipmentId
     * @return DataObject[]|ShipmentItemInterface[]
     */
    public function getShipmentItems($extShipmentId): array
    {
        $batch = $this->batchProvider->getBatch();

        $shipments = array_merge($batch->getFailedShipments(), $batch->getIncludedShipments());

        /** @var ShipmentSummaryInterface $shipment */
        foreach ($shipments as $shipment) {
            if ($shipment->getShipmentId() === $extShipmentId) {
                return $shipment->getItems();
            }
        }

        return [];
    }
}
