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
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Vertex\Data\CustomerInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Services\Quote\RequestInterface;
use Vertex\Services\Quote\RequestInterfaceFactory;
use Vertex\Tax\Model\AddressDeterminer;
use Vertex\Tax\Model\Api\Data\QuotationDeliveryTermProcessor;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\DateTimeImmutableFactory;
use Vertex\Tax\Model\IncompleteAddressDeterminer;

/**
 * Builds a Quotation Request for the Vertex SDK
 */
class QuotationRequestBuilder
{
    public const TRANSACTION_TYPE = 'SALE';

    /** @var AddressDeterminer */
    private $addressDeterminer;

    /** @var Config */
    private $config;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var OrderDeliveryTermProcessor */
    private $deliveryTerm;

    /** @var IncompleteAddressDeterminer */
    private $incompleteAddressDeterminer;

    /** @var LineItemBuilder */
    private $lineItemBuilder;

    /** @var RequestInterfaceFactory */
    private $requestFactory;

    /** @var SellerBuilder */
    private $sellerBuilder;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    public function __construct(
        LineItemBuilder $lineItemBuilder,
        RequestInterfaceFactory $requestFactory,
        CustomerBuilder $customerBuilder,
        SellerBuilder $sellerBuilder,
        Config $config,
        QuotationDeliveryTermProcessor $deliveryTerm,
        DateTimeImmutableFactory $dateTimeFactory,
        AddressDeterminer $addressDeterminer,
        StoreManagerInterface $storeManager,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        IncompleteAddressDeterminer $incompleteAddressDeterminer
    ) {
        $this->lineItemBuilder = $lineItemBuilder;
        $this->requestFactory = $requestFactory;
        $this->customerBuilder = $customerBuilder;
        $this->sellerBuilder = $sellerBuilder;
        $this->config = $config;
        $this->deliveryTerm = $deliveryTerm;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->addressDeterminer = $addressDeterminer;
        $this->storeManager = $storeManager;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->incompleteAddressDeterminer = $incompleteAddressDeterminer;
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

        /** @var RequestInterface $request */
        $request = $this->requestFactory->create();
        $request->setShouldReturnAssistedParameters(true);
        $request->setDocumentDate($this->dateTimeFactory->create());
        $request->setTransactionType(static::TRANSACTION_TYPE);
        $request->setCurrencyCode($this->storeManager->getStore($scopeCode)->getBaseCurrencyCode());

        $taxLineItems = $this->getLineItemData($quoteDetails, $scopeCode);
        $request->setLineItems($taxLineItems);

        $address = $this->addressDeterminer->determineAddress(
            $this->incompleteAddressDeterminer->isIncompleteAddress($quoteDetails->getShippingAddress()) ?
                $quoteDetails->getBillingAddress() :
                $quoteDetails->getShippingAddress(),
            $quoteDetails->getCustomerId() === null ? null : (int)$quoteDetails->getCustomerId(),
            $this->isVirtual($quoteDetails)
        );

        $seller = $this->sellerBuilder
            ->setScopeCode($scopeCode)
            ->setScopeType(ScopeInterface::SCOPE_STORE)
            ->build();

        $request->setSeller($seller);

        $taxClassKey = $quoteDetails->getCustomerTaxClassKey();
        if ($taxClassKey && $taxClassKey->getType() === TaxClassKeyInterface::TYPE_ID) {
            $customerTaxClassId = $taxClassKey->getValue();
        } else {
            $customerTaxClassId = $quoteDetails->getCustomerTaxClassId();
        }

        $request->setCustomer(
            $this->customerBuilder->buildFromCustomerAddress(
                $address,
                $quoteDetails->getCustomerId(),
                $customerTaxClassId,
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
     * @param CustomerInterface|null $customer
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

        /** @var CustomerInterface|null $billingCustomer */
        $billingCustomer = null;

        $itemsToCheck = array_merge($parentCodes, $processedItems);
        foreach ($items as $item) {
            if (in_array($item->getCode(), $itemsToCheck, true)) {
                // We merge these two arrays together as a convenience so we only need to run in_array once
                continue;
            }

            $qty = $item->getParentCode()
                ? $item->getQuantity() * $itemMap[$item->getParentCode()]->getQuantity()
                : $item->getQuantity();

            $customer = null;
            $isVirtual = $item->getExtensionAttributes()->getIsVirtual();

            if ($isVirtual) {
                // Use billing address for tax calculation on virtual line items
                if (!$billingCustomer) {
                    $address = $this->addressDeterminer->determineAddress(
                        $quoteDetails->getBillingAddress(),
                        $quoteDetails->getCustomerId() === null ? null : (int)$quoteDetails->getCustomerId(),
                        $isVirtual
                    );
                    $billingCustomer = $this->customerBuilder->buildFromCustomerAddress($address);
                }
                $customer = $billingCustomer;
            }

            $taxLineItems[] = $this->lineItemBuilder->buildFromQuoteDetailsItem($item, $qty, $scopeCode, $customer);
            $processedItems[] = $item->getCode();
            $itemsToCheck[] = $item->getCode();
        }

        return $taxLineItems;
    }

    /**
     * Determine if the Quote is virtual
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @return bool
     */
    private function isVirtual(QuoteDetailsInterface $quoteDetails)
    {
        foreach ($quoteDetails->getItems() as $item) {
            if ($item->getType() === 'shipping') {
                return false;
            }
        }

        return true;
    }
}
