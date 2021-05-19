<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Services\Quote\RequestInterface;
use Vertex\Services\Quote\RequestInterfaceFactory;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\DateTimeImmutableFactory;

/**
 * Builds a Quotation Request for the Vertex SDK
 */
class QuotationRequestBuilder
{
    public const TRANSACTION_TYPE = 'SALE';

    /** @var Config */
    private $config;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var OrderDeliveryTermProcessor */
    private $deliveryTerm;

    /** @var LineItemBuilder */
    private $lineItemBuilder;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var RequestInterfaceFactory */
    private $requestFactory;

    /** @var SellerBuilder */
    private $sellerBuilder;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var StringUtils */
    private $stringUtilities;

    public function __construct(
        LineItemBuilder $lineItemBuilder,
        RequestInterfaceFactory $requestFactory,
        CustomerBuilder $customerBuilder,
        SellerBuilder $sellerBuilder,
        Config $config,
        QuotationDeliveryTermProcessor $deliveryTerm,
        DateTimeImmutableFactory $dateTimeFactory,
        StoreManagerInterface $storeManager,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->lineItemBuilder = $lineItemBuilder;
        $this->requestFactory = $requestFactory;
        $this->customerBuilder = $customerBuilder;
        $this->sellerBuilder = $sellerBuilder;
        $this->config = $config;
        $this->deliveryTerm = $deliveryTerm;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->storeManager = $storeManager;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Create a properly formatted Quote Request for the Vertex API
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param string|null $scopeCode
     * @return RequestInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ConfigurationException
     */
    public function buildFromQuoteDetails(QuoteDetailsInterface $quoteDetails, $scopeCode = null)
    {
        $quoteMapper = $this->mapperFactory->getForClass(RequestInterface::class, $scopeCode);

        $request = $this->requestFactory->create();
        $request->setShouldReturnAssistedParameters(true);
        $request->setDocumentDate($this->dateTimeFactory->create());
        $request->setTransactionType(static::TRANSACTION_TYPE);
        $request->setCurrencyCode($this->storeManager->getStore($scopeCode)->getBaseCurrencyCode());

        $taxLineItems = $this->getLineItemData($quoteDetails, $scopeCode);
        $request->setLineItems($taxLineItems);

        $seller = $this->sellerBuilder
            ->setScopeCode($scopeCode)
            ->setScopeType(ScopeInterface::SCOPE_STORE)
            ->build();

        $request->setSeller($seller);

        $request->setCustomer(
            $this->customerBuilder->buildFromQuoteDetails(
                $quoteDetails,
                $scopeCode
            )
        );

        $this->deliveryTerm->addDeliveryTerm($request);

        $configLocationCode = $this->config->getLocationCode($scopeCode);

        if ($configLocationCode) {
            $locationCode = $this->stringUtilities->substr(
                $configLocationCode,
                0,
                $quoteMapper->getLocationCodeMaxLength()
            );
            $request->setLocationCode($locationCode);
        }

        return $request;
    }

    /**
     * Build Line Items for the Request
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param null $scopeCode
     * @return LineItemInterface[]
     * @throws ConfigurationException
     */
    private function getLineItemData(QuoteDetailsInterface $quoteDetails, $scopeCode = null)
    {
        // The resulting LineItemInterface[] to be used with Vertex
        $taxLineItems = [];

        // An array of codes for parent items
        $parentCodes = [];

        // A map of all items by their code
        $itemMap = [];

        // Item codes already processed - to prevent duplicates from bundles & configurables
        $processedItems = [];

        $items = $quoteDetails->getItems();
        foreach ($items as $item) {
            $itemMap[$item->getCode()] = $item;
            if ($item->getParentCode()) {
                $parentCodes[] = $item->getParentCode();
            }
        }

        $itemsToCheck = array_merge($parentCodes, $processedItems);
        foreach ($items as $item) {
            if (in_array($item->getCode(), $itemsToCheck, true)) {
                // We merge these two arrays together as a convenience so we only need to run in_array once
                continue;
            }

            $qty = $item->getParentCode()
                ? $item->getQuantity() * $itemMap[$item->getParentCode()]->getQuantity()
                : $item->getQuantity();

            $taxLineItems[] = $this->lineItemBuilder->buildFromQuoteDetailsItem($item, $qty, $scopeCode);
            $processedItems[] = $item->getCode();
            $itemsToCheck[] = $item->getCode();
        }

        return $taxLineItems;
    }
}
