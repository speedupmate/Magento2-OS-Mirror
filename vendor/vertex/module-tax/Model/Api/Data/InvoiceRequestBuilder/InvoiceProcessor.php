<?php

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Services\Invoice\RequestInterfaceFactory;
use Vertex\Tax\Model\Api\Data\CustomerBuilder;
use Vertex\Tax\Model\Api\Data\SellerBuilder;
use Vertex\Tax\Model\Api\Utility\DeliveryTerm;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\DateTimeImmutableFactory;

/**
 * Processes a Magento Invoice and returns a Vertex Invoice Request
 */
class InvoiceProcessor
{
    /** @var Config */
    private $config;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var DeliveryTerm */
    private $deliveryTerm;

    /** @var InvoiceProcessorInterface */
    private $processorPool;

    /** @var RequestInterfaceFactory */
    private $requestFactory;

    /** @var SellerBuilder */
    private $sellerBuilder;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @param RequestInterfaceFactory $requestFactory
     * @param DateTimeImmutableFactory $dateTimeFactory
     * @param SellerBuilder $sellerBuilder
     * @param CustomerBuilder $customerBuilder
     * @param DeliveryTerm $deliveryTerm
     * @param Config $config
     * @param InvoiceProcessorInterface $processorPool
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     */
    public function __construct(
        RequestInterfaceFactory $requestFactory,
        DateTimeImmutableFactory $dateTimeFactory,
        SellerBuilder $sellerBuilder,
        CustomerBuilder $customerBuilder,
        DeliveryTerm $deliveryTerm,
        Config $config,
        InvoiceProcessorInterface $processorPool,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->sellerBuilder = $sellerBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->deliveryTerm = $deliveryTerm;
        $this->config = $config;
        $this->processorPool = $processorPool;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Create a Vertex Invoice Request from a Magento Invoice
     *
     * @param InvoiceInterface $invoice
     * @return RequestInterface
     * @throws ConfigurationException
     */
    public function process(InvoiceInterface $invoice)
    {
        // If an invoice is virtual, it simply won't have a shipping address.
        /** @var OrderAddressInterface $address */
        $address = $invoice->getExtensionAttributes()->getVertexTaxCalculationShippingAddress()
            ?: $invoice->getExtensionAttributes()->getVertexTaxCalculationBillingAddress();

        /** @var OrderInterface $order */
        $order = $invoice->getExtensionAttributes()->getVertexTaxCalculationOrder();

        $scopeCode = $invoice->getStoreId();

        $seller = $this->sellerBuilder
            ->setScopeType(ScopeInterface::SCOPE_STORE)
            ->setScopeCode($scopeCode)
            ->build();

        $customer = $this->customerBuilder->buildFromOrderAddress(
            $address,
            $order->getCustomerId(),
            $order->getCustomerGroupId(),
            $scopeCode
        );

        $invoiceMapper = $this->mapperFactory->getForClass(RequestInterface::class, $scopeCode);

        /** @var RequestInterface $request */
        $request = $this->requestFactory->create();
        $request->setShouldReturnAssistedParameters(true);
        $request->setDocumentNumber($order->getIncrementId());
        $request->setDocumentDate($this->dateTimeFactory->create());
        $request->setTransactionType(RequestInterface::TRANSACTION_TYPE_SALE);
        $request->setSeller($seller);
        $request->setCustomer($customer);
        $request->setCurrencyCode($invoice->getBaseCurrencyCode());
        $this->deliveryTerm->addIfApplicable($request);

        $configLocationCode = $this->config->getLocationCode($scopeCode);

        if ($configLocationCode) {
            $locationCode = $this->stringUtilities->substr($configLocationCode, 0, $invoiceMapper->getLocationCodeMaxLength());
            $request->setLocationCode($locationCode);
        }

        $request = $this->processorPool->process($request, $invoice);

        return $request;
    }
}
