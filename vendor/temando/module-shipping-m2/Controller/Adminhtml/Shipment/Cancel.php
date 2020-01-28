<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Model\Sales\Service\ShipmentService;

/**
 * Temando Shipment Cancellation Action
 *
 * @package Temando\Shipping\Controller
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Cancel extends Action
{
    const ADMIN_RESOURCE = 'Temando_Shipping::shipping';

    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * Cancel constructor.
     *
     * @param Context $context
     * @param ShipmentService $shipmentService
     */
    public function __construct(
        Context $context,
        ShipmentService $shipmentService
    ) {
        $this->shipmentService = $shipmentService;

        parent::__construct($context);
    }

    /**
     * Trigger shipment cancellation at the platform, inform user about result.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $salesShipmentId = (int) $this->getRequest()->getParam('sales_shipment_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');

        try {
            $this->shipmentService->cancel($shipmentId, $salesShipmentId);
            $this->messageManager->addSuccessMessage(__('Shipment was successfully cancelled.'));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/shipment/view', ['shipment_id' => $salesShipmentId]);

        return $resultRedirect;
    }
}
