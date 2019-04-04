<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\ScopeInterface;
use Vertex\Services\Invoice\ResponseInterface;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\InvoiceSentRegistry;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Model\VertexTaxAttributeManager;

/**
 * Observes when an Invoice is issued to fire off data to the Vertex Tax Log
 */
class InvoiceSavedAfterObserver implements ObserverInterface
{
    /** @var VertexCalculationExtensionLoader */
    private $vertexExtensionLoader;

    /** @var Config */
    private $config;

    /** @var ConfigurationValidator */
    private $configValidator;

    /** @var CountryGuard */
    private $countryGuard;

    /** @var GiftwrapExtensionLoader */
    private $extensionLoader;

    /** @var InvoiceRequestBuilder */
    private $invoiceRequestBuilder;

    /** @var InvoiceSentRegistry */
    private $invoiceSentRegistry;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var TaxInvoice */
    private $taxInvoice;

    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var bool */
    private $showSuccessMessage;

    /**
     * @param Config $config
     * @param CountryGuard $countryGuard
     * @param TaxInvoice $taxInvoice
     * @param ManagerInterface $messageManager
     * @param InvoiceSentRegistry $invoiceSentRegistry
     * @param ConfigurationValidator $configValidator
     * @param InvoiceRequestBuilder $invoiceRequestBuilder
     * @param GiftwrapExtensionLoader $extensionLoader
     * @param VertexTaxAttributeManager $attributeManager
     * @param VertexCalculationExtensionLoader $vertexExtensionLoader
     * @param bool $showSuccessMessage
     */
    public function __construct(
        Config $config,
        CountryGuard $countryGuard,
        TaxInvoice $taxInvoice,
        ManagerInterface $messageManager,
        InvoiceSentRegistry $invoiceSentRegistry,
        ConfigurationValidator $configValidator,
        InvoiceRequestBuilder $invoiceRequestBuilder,
        GiftwrapExtensionLoader $extensionLoader,
        VertexTaxAttributeManager $attributeManager,
        VertexCalculationExtensionLoader $vertexExtensionLoader,
        $showSuccessMessage = false
    ) {
        $this->config = $config;
        $this->countryGuard = $countryGuard;
        $this->taxInvoice = $taxInvoice;
        $this->messageManager = $messageManager;
        $this->invoiceSentRegistry = $invoiceSentRegistry;
        $this->configValidator = $configValidator;
        $this->invoiceRequestBuilder = $invoiceRequestBuilder;
        $this->extensionLoader = $extensionLoader;
        $this->attributeManager = $attributeManager;
        $this->showSuccessMessage = $showSuccessMessage;
        $this->vertexExtensionLoader = $vertexExtensionLoader;
    }

    /**
     * Commit an invoice to the Vertex Tax Log
     *
     * Only when Request by Invoice Creation is turned on
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $storeId = $invoice->getStoreId();
        if (!$this->config->isVertexActive($storeId) || !$this->config->isTaxCalculationEnabled($storeId)) {
            return;
        }

        $this->extensionLoader->loadOnInvoice($invoice);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();

        /** @var boolean $isInvoiceSent */
        $isInvoiceSent = $this->invoiceSentRegistry->hasInvoiceBeenSentToVertex($invoice);

        /** @var boolean $requestByInvoice */
        $requestByInvoice = $this->config->requestByInvoiceCreation($invoice->getStore());

        /** @var boolean $canService */
        $canService = $this->countryGuard->isOrderServiceableByVertex($order);

        /** @var boolean $configValid */
        $configValid = $this->configValidator->execute(ScopeInterface::SCOPE_STORE, $invoice->getStoreId(), true)
            ->isValid();

        if (!$isInvoiceSent && $requestByInvoice && $canService && $configValid) {
            // During checkout for authorize & capture, the invoice will not have the address IDs
            $invoice = $this->vertexExtensionLoader->loadOnInvoice($invoice);
            $request = $this->invoiceRequestBuilder->buildFromInvoice($invoice);
            $response = $this->taxInvoice->sendInvoiceRequest($request, $invoice->getOrder());
            $this->processResponse($response, $invoice);
        }
    }

    /**
     * Process response
     *
     * @param null|ResponseInterface $response
     * @param Invoice $invoice
     * @return void
     */
    private function processResponse($response, $invoice)
    {
        if ($response) {
            $this->attributeManager->saveAllVertexAttributes($response->getLineItems());
            $this->invoiceSentRegistry->setInvoiceHasBeenSentToVertex($invoice);
            $this->addSuccessMessage();
        }
    }

    /**
     * Notify administrator that the order has been committed to the tax log
     *
     * @return void
     */
    private function addSuccessMessage()
    {
        if ($this->showSuccessMessage) {
            $this->messageManager->addSuccessMessage(__('The Vertex invoice has been sent.')->render());
        }
    }
}
