<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Store\Model\ScopeInterface;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\OrderHasInvoiceDeterminer;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Model\VertexTaxAttributeManager;
use Vertex\Tax\Model\Loader\GiftwrapExtensionLoader;

/**
 * Observes when a Creditmemo is issued to fire off data to the Vertex Tax Log
 */
class CreditMemoObserver implements ObserverInterface
{
    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var Config */
    private $config;

    /** @var ConfigurationValidator */
    private $configValidator;

    /** @var CountryGuard */
    private $countryGuard;

    /** @var GiftwrapExtensionLoader */
    private $extensionLoader;

    /** @var OrderHasInvoiceDeterminer */
    private $hasInvoiceDeterminer;

    /** @var InvoiceRequestBuilder */
    private $invoiceRequestBuilder;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var TaxInvoice */
    private $taxInvoice;

    /**
     * @param Config $config
     * @param CountryGuard $countryGuard
     * @param TaxInvoice $taxInvoice
     * @param ManagerInterface $messageManager
     * @param ConfigurationValidator $configValidator
     * @param InvoiceRequestBuilder $invoiceRequestBuilder
     * @param GiftwrapExtensionLoader $extensionLoader
     * @param VertexTaxAttributeManager $attributeManager
     * @param OrderHasInvoiceDeterminer $hasInvoiceDeterminer
     */
    public function __construct(
        Config $config,
        CountryGuard $countryGuard,
        TaxInvoice $taxInvoice,
        ManagerInterface $messageManager,
        ConfigurationValidator $configValidator,
        InvoiceRequestBuilder $invoiceRequestBuilder,
        GiftwrapExtensionLoader $extensionLoader,
        VertexTaxAttributeManager $attributeManager,
        OrderHasInvoiceDeterminer $hasInvoiceDeterminer
    ) {
        $this->config = $config;
        $this->countryGuard = $countryGuard;
        $this->taxInvoice = $taxInvoice;
        $this->messageManager = $messageManager;
        $this->configValidator = $configValidator;
        $this->invoiceRequestBuilder = $invoiceRequestBuilder;
        $this->extensionLoader = $extensionLoader;
        $this->hasInvoiceDeterminer = $hasInvoiceDeterminer;
        $this->attributeManager = $attributeManager;
    }

    /**
     * Commit a creditmemo to the Vertex Tax Log on Creditmemo Creation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditMemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();
        if (!$this->config->isVertexActive($creditMemo->getStoreId())) {
            return;
        }

        /** @var Order $order */
        $order = $creditMemo->getOrder();

        if ($this->canSend($creditMemo, $order) && $this->hasInvoiceDeterminer->hasInvoice($order->getId())) {
            $creditMemo = $this->extensionLoader->loadOnCreditmemo($creditMemo);
            $request = $this->invoiceRequestBuilder->buildFromCreditmemo($creditMemo);
            $response = $this->taxInvoice->sendRefundRequest($request, $order);

            if ($response) {
                $this->attributeManager->saveAllVertexAttributes($response->getLineItems());
                $this->messageManager->addSuccessMessage(__('The Vertex invoice has been refunded.')->render());
            }
        }
    }

    /**
     * Verify if creditmemo can be sent
     *
     * @param Creditmemo $creditMemo
     * @param Order $order
     * @return bool
     */
    private function canSend(Creditmemo $creditMemo, Order $order)
    {
        return $this->countryGuard->isOrderServiceableByVertex($order)
            && $this->configValidator
                ->execute(ScopeInterface::SCOPE_STORE, $creditMemo->getStoreId(), true)
                ->isValid();
    }
}
