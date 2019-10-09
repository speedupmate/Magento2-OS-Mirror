<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Batch;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Controller\Adminhtml\Order\Pdfshipments;

/**
 * Temando Batch Print Action
 *
 * @package Temando\Shipping\Controller
 * @author  Sebastian Ertner <sebastian.ertner@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PrintAction extends Pdfshipments
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Temando_Shipping::batches';

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return (
            $this->_authorization->isAllowed(static::ADMIN_RESOURCE) &&
            $this->_authorization->isAllowed('Magento_Sales::ship') &&
            $this->_authorization->isAllowed('Magento_Sales::shipment')
        );
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath($this->_redirect->getRefererUrl());

        $collection = $this->shipmentCollectionFactory->create();
        $collection->addFieldToFilter(ShipmentInterface::ENTITY_ID, ['in' => $shipmentIds]);

        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents in this batch.'));
            return $redirect;
        }

        try {
            $filename = sprintf('packingslip-%s.pdf', $this->dateTime->date('Y-m-d_H-i-s'));
            $pdf = $this->pdfShipment->getPdf($collection->getItems());
            $content = $pdf->render();
            $response = $this->fileFactory->create($filename, $content, DirectoryList::VAR_DIR, 'application/pdf');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while creating the packing slips.'));
            return $redirect;
        }

        return $response;
    }
}
