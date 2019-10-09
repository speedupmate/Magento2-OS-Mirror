<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Block\Adminhtml\PageAction;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Model\BatchProviderInterface;
use Temando\Shipping\Model\ResourceModel\Shipment\ShipmentReferenceCollection;
use Temando\Shipping\Model\ResourceModel\Shipment\ShipmentReferenceCollectionFactory;
use Temando\Shipping\ViewModel\DataProvider\BatchUrl;

/**
 * Action Button to Batch Print Page
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BatchPrintButton extends Button
{
    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var BatchUrl
     */
    private $batchUrl;

    /**
     * @var ShipmentReferenceCollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * BatchPrintButton constructor.
     * @param Context $context
     * @param BatchProviderInterface $batchProvider
     * @param BatchUrl $batchUrl
     * @param ShipmentReferenceCollectionFactory $shipmentCollectionFactory
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        BatchProviderInterface $batchProvider,
        BatchUrl $batchUrl,
        ShipmentReferenceCollectionFactory $shipmentCollectionFactory,
        array $data = []
    ) {
        $this->batchProvider = $batchProvider;
        $this->batchUrl = $batchUrl;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;

        parent::__construct($context, $data);
    }

    /**
     * Add button data
     *
     * @return string
     */
    protected function _toHtml()
    {
        $batch = $this->batchProvider->getBatch();

        $shipmentIds = array_keys($batch->getIncludedShipments());
        if (empty($shipmentIds)) {
            // no successfully booked shipments in this batch
            return '';
        }

        /** @var ShipmentReferenceCollection $collection */
        $collection = $this->shipmentCollectionFactory->create();
        $collection->addFieldToFilter(ShipmentReferenceInterface::EXT_SHIPMENT_ID, ['in' => $shipmentIds]);
        if (!$collection->getSize()) {
            // shipment synchronization pending
            return '';
        }

        $printBatchUrl = $this->batchUrl->getPrintActionUrl([
            'shipment_ids' => $collection->getColumnValues(ShipmentReferenceInterface::SHIPMENT_ID)
        ]);

        $this->setData('label', __('Print All Packing Slips'));
        $this->setData('class', 'print');
        $this->setData('id', 'batch-view-print-button');
        $this->setData('level', -1);
        $this->setData('onclick', sprintf("setLocation('%s')", $printBatchUrl));

        return parent::_toHtml();
    }
}
