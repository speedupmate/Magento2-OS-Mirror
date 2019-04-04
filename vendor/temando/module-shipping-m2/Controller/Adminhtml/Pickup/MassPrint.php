<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Controller\Adminhtml\Pickup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Temando\Shipping\Model\Pickup;
use Temando\Shipping\Model\Pickup\Pdf\PickupPdfFactory;
use Temando\Shipping\Model\Pickup\PickupLoader;
use Temando\Shipping\Model\PickupInterface;
use Temando\Shipping\Model\PickupProviderInterface;
use Temando\Shipping\Model\ResourceModel\Pickup\Grid\Collection;
use Temando\Shipping\Model\ResourceModel\Pickup\Grid\CollectionFactory;
use Temando\Shipping\Ui\Component\MassAction\Filter;

/**
 * Temando Mass Print Action
 *
 * @package Temando\Shipping\Controller
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class MassPrint extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Temando_Shipping::pickups';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var PickupLoader
     */
    private $pickupLoader;

    /**
     * @var PickupProviderInterface
     */
    private $pickupProvider;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var PickupPdfFactory
     */
    private $pickupPdfFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param PickupLoader $pickupLoader
     * @param PickupProviderInterface $pickupProvider
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param PickupPdfFactory $pickupPdfFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        PickupLoader $pickupLoader,
        PickupProviderInterface $pickupProvider,
        FileFactory $fileFactory,
        DateTime $dateTime,
        PickupPdfFactory $pickupPdfFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->pickupLoader = $pickupLoader;
        $this->pickupProvider = $pickupProvider;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pickupPdfFactory = $pickupPdfFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam(\Magento\Ui\Component\MassAction\Filter::SELECTED_PARAM, []);
        $excluded = $this->getRequest()->getParam(\Magento\Ui\Component\MassAction\Filter::EXCLUDED_PARAM, []);
        if ($excluded === 'false') {
            $excluded = [];
        }

        $pickupCollection = $this->collectionFactory->create();
        $pickupCollection->setItemObjectClass(Pickup::class);
        $pickupIds = $this->filter->getPickupIds($pickupCollection, $selected, $excluded);

        try {
            $downloadResponse = $this->createPackagingSlips($pickupCollection, $pickupIds);
            return $downloadResponse;
        } catch (\InvalidArgumentException $e) {
            $this->messageManager->addErrorMessage(__('There are no packing slips related to selected pickups.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error creating package slip pdf.'));
        }

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath($this->_redirect->getRefererUrl());
        return $redirect;
    }

    /**
     * @param Collection $pickupCollection
     * @param string[] $pickupIds
     *
     * @return ResponseInterface|Redirect
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    private function createPackagingSlips($pickupCollection, $pickupIds)
    {
        $pickups = $pickupCollection->getItems();
        $labelsContent = array_reduce($pickups, function (array $carry, PickupInterface $pickup) use ($pickupIds) {
            if (!in_array($pickup->getPickupId(), $pickupIds)) {
                return $carry;
            }

            $this->pickupLoader->register(
                [$pickup->getPickupId() => $pickup],
                (int)$pickup->getSalesOrderId(),
                (string)$pickup->getPickupId()
            );

            $pickup = $this->pickupProvider->getPickup();
            $order = $this->pickupProvider->getOrder();

            $pickupPdf = $this->pickupPdfFactory->create(
                ['data' => ['order' => $order, 'pickup' => $pickup, 'pickups' => [$pickup]]]
            );

            try {
                $carry[] = $pickupPdf->getPdf();
                return $carry;
            } catch (\Zend_Pdf_Exception $exception) {
                return $carry;
            }
        }, []);

        if (empty($labelsContent)) {
            throw new \InvalidArgumentException('Please select pickup IDs.');
        }

        $outputPdf = new \Zend_Pdf();

        $pages = array_reduce($labelsContent, function ($carry, \Zend_Pdf $pdf) {
            foreach ($pdf->pages as $page) {
                $carry[] = $page;
            }
            return $carry;
        }, []);

        $outputPdf->pages = $pages;

        return $this->fileFactory->create(
            sprintf('packingslips-%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $outputPdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
