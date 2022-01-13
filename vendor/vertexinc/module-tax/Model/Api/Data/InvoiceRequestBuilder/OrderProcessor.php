<?php

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Services\Invoice\RequestInterfaceFactory;
use Vertex\Tax\Model\Api\Data\CustomerBuilder;
use Vertex\Tax\Model\Api\Data\SellerBuilder;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\DateTimeImmutableFactory;

/**
 * Processes a Magento Order and returns a Vertex Invoice Request
 */
class OrderProcessor
{
    /** @var Config */
    private $config;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var OrderProcessorInterface */
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
     * @param Config $config
     * @param OrderProcessorInterface $processorPool
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     */
    public function __construct(
        RequestInterfaceFactory $requestFactory,
        DateTimeImmutableFactory $dateTimeFactory,
        SellerBuilder $sellerBuilder,
        CustomerBuilder $customerBuilder,
        Config $config,
        OrderProcessorInterface $processorPool,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->sellerBuilder = $sellerBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->config = $config;
        $this->processorPool = $processorPool;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Create a Vertex Invoice Request from a Magento Order
     *
     * @param OrderInterface $order
     * @return RequestInterface
     * @throws ConfigurationException
     */
    public function process(OrderInterface $order)
    {
        $scopeCode = $order->getStoreId();

        $seller = $this->sellerBuilder
            ->setScopeType(ScopeInterface::SCOPE_STORE)
            ->setScopeCode($scopeCode)
            ->build();

        $customer = $this->customerBuilder->buildFromOrder($order);

        $invoiceMapper = $this->mapperFactory->getForClass(RequestInterface::class, $scopeCode);

        /** @var RequestInterface $request */
        $request = $this->requestFactory->create();
        $request->setShouldReturnAssistedParameters(true);
        $request->setDocumentNumber($order->getIncrementId());
        $request->setDocumentDate($this->dateTimeFactory->create());
        $request->setTransactionType(RequestInterface::TRANSACTION_TYPE_SALE);
        $request->setSeller($seller);
        $request->setCustomer($customer);
        $request->setCurrencyCode($order->getBaseCurrencyCode());

        $configLocationCode = $this->config->getLocationCode($scopeCode);

        if ($configLocationCode) {
            $locationCode = $this->stringUtilities->substr(
                $configLocationCode,
                0,
                $invoiceMapper->getLocationCodeMaxLength()
            );
            $request->setLocationCode($locationCode);
        }

        $request = $this->processorPool->process($request, $order);

        return $request;
    }
}
