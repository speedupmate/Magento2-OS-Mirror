<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Services\Invoice\ResponseInterface;
use Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ConfigurationValidator;
use Vertex\Tax\Model\CountryGuard;
use Vertex\Tax\Model\Data\OrderInvoiceStatus;
use Vertex\Tax\Model\Data\OrderInvoiceStatusFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\GuestAfterPaymentWorkaroundService;
use Vertex\Tax\Model\Repository\OrderInvoiceStatusRepository;
use Vertex\Tax\Model\TaxInvoice;
use Vertex\Tax\Model\VertexTaxAttributeManager;
use Vertex\Tax\Model\Loader\ShippingAssignmentExtensionLoader;
use Vertex\Tax\Model\Loader\GiftwrapExtensionLoader;

/**
 * Observes when an Order is saved to determine if we need to commit data to the Vertex Tax Log
 */
class OrderSavedAfterObserver implements ObserverInterface
{
    /** @var VertexTaxAttributeManager */
    private $attributeManager;

    /** @var Config */
    private $config;

    /** @var ConfigurationValidator */
    private $configValidator;

    /** @var CountryGuard */
    private $countryGuard;

    /** @var OrderInvoiceStatusFactory */
    private $factory;

    /** @var GiftwrapExtensionLoader */
    private $giftwrapExtensionLoader;

    /** @var InvoiceRequestBuilder */
    private $invoiceRequestBuilder;

    /** @var ExceptionLogger */
    private $logger;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var OrderInvoiceStatusRepository */
    private $repository;

    /** @var ShippingAssignmentExtensionLoader */
    private $shipmentExtensionLoader;

    /** @var bool */
    private $showSuccessMessage;

    /** @var TaxInvoice */
    private $taxInvoice;

    /** @var GuestAfterPaymentWorkaroundService */
    private $workaroundService;

    /**
     * @param Config $config
     * @param CountryGuard $countryGuard
     * @param TaxInvoice $taxInvoice
     * @param ManagerInterface $messageManager
     * @param OrderInvoiceStatusRepository $repository
     * @param OrderInvoiceStatusFactory $factory
     * @param ExceptionLogger $logger
     * @param ConfigurationValidator $configValidator
     * @param InvoiceRequestBuilder $invoiceRequestBuilder
     * @param GiftwrapExtensionLoader $giftwrapExtensionLoader
     * @param ShippingAssignmentExtensionLoader $shipmentExtensionLoader
     * @param VertexTaxAttributeManager $attributeManager
     * @param GuestAfterPaymentWorkaroundService $workaroundService
     * @param bool $showSuccessMessage
     */
    public function __construct(
        Config $config,
        CountryGuard $countryGuard,
        TaxInvoice $taxInvoice,
        ManagerInterface $messageManager,
        OrderInvoiceStatusRepository $repository,
        OrderInvoiceStatusFactory $factory,
        ExceptionLogger $logger,
        ConfigurationValidator $configValidator,
        InvoiceRequestBuilder $invoiceRequestBuilder,
        GiftwrapExtensionLoader $giftwrapExtensionLoader,
        ShippingAssignmentExtensionLoader $shipmentExtensionLoader,
        VertexTaxAttributeManager $attributeManager,
        GuestAfterPaymentWorkaroundService $workaroundService,
        $showSuccessMessage = false
    ) {
        $this->config = $config;
        $this->countryGuard = $countryGuard;
        $this->taxInvoice = $taxInvoice;
        $this->messageManager = $messageManager;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->logger = $logger;
        $this->configValidator = $configValidator;
        $this->invoiceRequestBuilder = $invoiceRequestBuilder;
        $this->giftwrapExtensionLoader = $giftwrapExtensionLoader;
        $this->shipmentExtensionLoader = $shipmentExtensionLoader;
        $this->attributeManager = $attributeManager;
        $this->showSuccessMessage = $showSuccessMessage;
        $this->workaroundService = $workaroundService;
    }

    /**
     * Commit an Invoice to the Vertex Tax Log
     *
     * When an order is saved, request by order status is enabled, and the Order's status is the one configured, we
     * will commit it's data to the Vertex Tax Log
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->workaroundService->clearOrders();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$this->config->isVertexActive($order->getStoreId())
            || $this->hasInvoice($order->getId())
            || !$this->config->isTaxCalculationEnabled($order->getStoreId())
        ) {
            return;
        }

        /** @var boolean $requestByOrder */
        $requestByOrder = $this->requestByOrderStatus($order->getStatus(), $order->getStore());

        /** @var boolean $canService */
        $canService = $this->countryGuard->isOrderServiceableByVertex($order);

        /** @var boolean $configValid */
        $configValid = $this->configValidator->execute(ScopeInterface::SCOPE_STORE, $order->getStoreId(), true)
            ->isValid();

        if ($requestByOrder && $canService && $configValid) {
            // We perform a cloning operation here to prevent tampering with the original order during placement
            $order = clone $order;
            if ($order->getExtensionAttributes()) {
                $order->setExtensionAttributes(clone $order->getExtensionAttributes());
            }

            $order = $this->shipmentExtensionLoader->loadOnOrder($order);
            $order = $this->giftwrapExtensionLoader->loadOnOrder($order);
            $request = $this->invoiceRequestBuilder->buildFromOrder($order);

            /** @var ResponseInterface */
            $response = $this->taxInvoice->sendInvoiceRequest($request, $order);

            $this->processResponse($response, $order->getId());
        }
    }

    /**
     * Notify administrator that the invoice has been committed to the tax log
     *
     * @return void
     */
    private function addSuccessMessage()
    {
        if ($this->showSuccessMessage) {
            $this->messageManager->addSuccessMessage(__('The Vertex invoice has been sent.')->render());
        }
    }

    /**
     * Determine if an Order already has an invoice
     *
     * @param int $orderId
     * @return bool
     */
    private function hasInvoice($orderId)
    {
        try {
            $this->repository->getByOrderId($orderId);
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Process vertex response
     *
     * @param ResponseInterface|null $response
     * @param int $orderId
     * @return void
     */
    private function processResponse($response, $orderId)
    {
        if ($response) {
            /** @var LineItemInterface[] $items */
            if ($items = $response->getLineItems()) {
                $this->attributeManager->saveAllVertexAttributes($items);
            }
            $this->addSuccessMessage();
            $this->setHasInvoice($orderId);
        }
    }

    /**
     * Determine if we should commit to the tax log on this order status
     *
     * Checks if request by order status is enabled and that our status matches the one configured
     *
     * @param string $status
     * @param string|null $store
     * @return bool
     */
    private function requestByOrderStatus($status, $store = null)
    {
        return $this->config->requestByOrderStatus($store) && $status === $this->config->invoiceOrderStatus($store);
    }

    /**
     * Register that an Order already has an Invoice
     *
     * @param int $orderId
     * @return void
     */
    private function setHasInvoice($orderId)
    {
        /** @var OrderInvoiceStatus $orderInvoiceStatus */
        try {
            $orderInvoiceStatus = $this->repository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $orderInvoiceStatus = $this->factory->create();
            $orderInvoiceStatus->setId($orderId);
        }
        $orderInvoiceStatus->setIsSent(true);
        try {
            $this->repository->save($orderInvoiceStatus);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
