<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Batch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Model\BatchProviderInterface;
use Temando\Shipping\Model\ResourceModel\Repository\BatchRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Shipment\ShipmentReferenceCollectionFactory;

/**
 * Temando View Batch Action
 *
 * @package Temando\Shipping\Controller
 * @author  Rhodri Davies <rhodri.davies@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class View extends AbstractBatchAction
{
    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var ShipmentReferenceCollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param BatchRepositoryInterface $batchRepository
     * @param BatchProviderInterface $batchProvider
     * @param ShipmentReferenceCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        BatchRepositoryInterface $batchRepository,
        BatchProviderInterface $batchProvider,
        ShipmentReferenceCollectionFactory $shipmentCollectionFactory
    ) {
        $this->batchProvider = $batchProvider;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;

        parent::__construct($context, $batchRepository, $batchProvider);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return (
            $this->_authorization->isAllowed(static::ADMIN_RESOURCE) &&
            $this->_authorization->isAllowed('Magento_Sales::shipment')
        );
    }

    /**
     * Render template.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $batch = $this->batchProvider->getBatch();
        $shipmentIds = array_keys($batch->getIncludedShipments());
        if (!empty($shipmentIds)) {
            $collection = $this->shipmentCollectionFactory->create();
            $collection->addFieldToFilter(ShipmentReferenceInterface::EXT_SHIPMENT_ID, ['in' => $shipmentIds]);

            // compare local shipments with platform shipments. if size differs, then synchronization is pending.
            // @codingStandardsIgnoreStart
            if ($collection->getSize() !== count($shipmentIds)) {
                $message = __('There are shipments in this batch that are still being created. Please check back soon to view these shipments.');
                $this->messageManager->addWarningMessage($message);
            }
            // @codingStandardsIgnoreEnd
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Temando_Shipping::batches');
        $resultPage->getConfig()->getTitle()->prepend(__('Batches'));
        $resultPage->addBreadcrumb(__('Batches'), __('Batches'), $this->getUrl('temando/batch'));

        return $resultPage;
    }
}
