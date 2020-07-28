<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Block\Info;

use Klarna\Core\Model\MerchantPortal;
use Klarna\Core\Model\OrderRepository;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Klarna\Core\Api\OrderInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrder;

/**
 * @api
 */
class Klarna extends \Magento\Payment\Block\Info
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;
    /**
     *
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var Resolver
     */
    private $locale;
    /**
     * @var MerchantPortal
     */
    private $merchantPortal;
    /**
     * @var State
     */
    private $appState;

    /**
     * @param Context           $context
     * @param OrderRepository   $orderRepository
     * @param MerchantPortal    $merchantPortal
     * @param Resolver          $locale
     * @param DataObjectFactory $dataObjectFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        MerchantPortal $merchantPortal,
        Resolver $locale,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->_template = 'Klarna_Core::payment/info.phtml';
        $this->locale = $locale;
        $this->merchantPortal = $merchantPortal;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->appState = $context->getAppState();
    }

    /**
     * Return locale info
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale->getLocale();
    }

    /**
     * Get specific information for the invoice pdf.
     *
     * @return array
     */
    public function getSpecificInformation(): array
    {
        $result = $this->getDisplayedInformation();
        $result->unsetData((string)__('Merchant Portal'));

        return $result->getData();
    }

    /**
     * Get specific information for the payment section in the admin order page.
     *
     * @return array
     */
    public function getFullSpecificInformation(): array
    {
        $result = $this->getDisplayedInformation();
        return $result->getData();
    }

    /**
     * Getting all displayed information
     *
     * @return DataObject
     * @throws LocalizedException
     */
    private function getDisplayedInformation(): DataObject
    {
        $data = parent::getSpecificInformation();
        $transport = $this->dataObjectFactory->create(['data' => $data]);
        $info = $this->getInfo();
        $order = $info->getOrder();
        try {
            $klarnaOrder = $this->orderRepository->getByOrder($order);

            if ($klarnaOrder->getId() && $klarnaOrder->getKlarnaOrderId()) {
                $transport->setData((string)__('Order ID'), $klarnaOrder->getKlarnaOrderId());

                $this->addReservationToDisplay($transport, $klarnaOrder);
                $this->addMerchantPortalLinkToDisplay($transport, $order, $klarnaOrder);
            }
        } catch (NoSuchEntityException $e) {
            $transport->setData((string)__('Error'), $e->getMessage());
        }

        $klarnaReferenceId = $info->getAdditionalInformation('klarna_reference');
        if ($klarnaReferenceId) {
            $transport->setData((string)__('Reference'), $klarnaReferenceId);
        }

        $this->addInvoicesToDisplay($transport, $order);

        return $transport;
    }

    /**
     * Add Klarna Reservation ID to order view
     *
     * @param DataObject     $transport
     * @param OrderInterface $klarnaOrder
     */
    private function addReservationToDisplay(DataObject $transport, OrderInterface $klarnaOrder)
    {
        if ($klarnaOrder->getReservationId()
            && $klarnaOrder->getReservationId() != $klarnaOrder->getKlarnaOrderId()
        ) {
            $transport->setData((string)__('Reservation'), $klarnaOrder->getReservationId());
        }
    }

    /**
     * Add Klarna Merchant Portal link to order view
     *
     * @param DataObject     $transport
     * @param MagentoOrder   $order
     * @param OrderInterface $klarnaOrder
     */
    private function addMerchantPortalLinkToDisplay(
        DataObject $transport,
        MagentoOrder $order,
        OrderInterface $klarnaOrder
    ) {
        if ($this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $merchantPortalLink = $this->merchantPortal->getOrderMerchantPortalLink($order, $klarnaOrder);
            if ($merchantPortalLink) {
                $transport->setData(
                    (string)__('Merchant Portal'),
                    $this->merchantPortal->getOrderMerchantPortalLink($order, $klarnaOrder)
                );
            }
        }
    }

    /**
     * Add invoices to order view
     *
     * @param DataObject $transport
     * @param MagentoOrder $order
     */
    private function addInvoicesToDisplay(DataObject $transport, MagentoOrder $order)
    {
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            if ($invoice->getTransactionId()) {
                $invoiceKey = (string)__('Invoice ID (#%1)', $invoice->getIncrementId());
                $transport->setData($invoiceKey, $invoice->getTransactionId());
            }
        }
    }

    /**
     * Check if string is a url
     *
     * @param string $string
     * @return bool
     */
    public function isStringUrl($string)
    {
        return (bool)filter_var($string, FILTER_VALIDATE_URL);
    }
}
