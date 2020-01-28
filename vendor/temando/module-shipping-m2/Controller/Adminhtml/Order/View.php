<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Temando\Shipping\Api\Data\Shipment\ShipmentReferenceInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface;

/**
 * Temando Redirect Order Page
 *
 * Query an Order ID based on given Platform Shipment ID and redirect to native order page.
 *
 * @package Temando\Shipping\Controller
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class View extends Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::actions_view';

    /**
     * @var ShipmentReferenceRepositoryInterface
     */
    private $shipmentReferenceRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param ShipmentReferenceRepositoryInterface $shipmentReferenceRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param FilterBuilder $filterBuilder
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        Escaper $escaper
    ) {
        $this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        $extShipmentId = $this->escaper->escapeHtml($this->getRequest()->getParam('shipment_id'));

        $shipmentFilter = $this->filterBuilder
            ->setField(ShipmentReferenceInterface::EXT_SHIPMENT_ID)
            ->setValue($extShipmentId)
            ->setConditionType('eq')
            ->create();
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter($shipmentFilter)
            ->setPageSize(1)
            ->create();

        $collection = $this->shipmentReferenceRepository->getList($searchCriteria);
        $shipment = $collection->fetchItem();
        if (!$shipment) {
            $this->messageManager->addErrorMessage(__("Shipment '$extShipmentId' not found."));

            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');

            return $resultForward;
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $shipment->getData('order_id')]);
        return $resultRedirect;
    }
}
