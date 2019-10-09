<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Model\Config\ModuleConfig;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Rma\RmaShipment;

/**
 * Temando Redirect Shipment Page
 *
 * Query a Shipment ID based on given Platform ID and redirect to native shipment page.
 *
 * @package Temando\Shipping\Controller
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class View extends Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var ShipmentReferenceRepositoryInterface
     */
    private $shipmentReferenceRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var RmaShipment
     */
    private $rmaShipment;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param ShipmentReferenceRepositoryInterface $shipmentReferenceRepository
     * @param Escaper $escaper
     * @param ModuleConfig|null $config
     * @param RmaShipment|null $rmaShipment
     */
    public function __construct(
        Context $context,
        ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        Escaper $escaper,
        ModuleConfig $config = null,
        RmaShipment $rmaShipment = null
    ) {
        $this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->escaper = $escaper;
        $this->config = $config ?: ObjectManager::getInstance()->get(ModuleConfig::class);
        $this->rmaShipment = $rmaShipment ?: ObjectManager::getInstance()->get(RmaShipment::class);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\AbstractResult
     */
    public function execute()
    {
        $extShipmentId = $this->escaper->escapeHtml($this->getRequest()->getParam('shipment_id'));

        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if ($this->config->isRmaAvailable()) {
                $rmaShipmentId = $this->rmaShipment->getIdByExtShipmentId($extShipmentId);
                if ($rmaShipmentId) {
                    $resultRedirect->setPath(
                        'temando/rma_shipment/view',
                        ['rma_id' => $rmaShipmentId, 'ext_shipment_id' => $extShipmentId]
                    );
                    return $resultRedirect;
                }
            }

            $shipmentReference = $this->shipmentReferenceRepository->getByExtShipmentId($extShipmentId);
            $resultRedirect->setPath('sales/shipment/view', ['shipment_id' => $shipmentReference->getShipmentId()]);
        } catch (LocalizedException $exception) {
            $message = "Shipment '$extShipmentId' not found.";
            $this->messageManager->addExceptionMessage($exception, __($message));

            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');

            return $resultForward;
        }

        return $resultRedirect;
    }
}
