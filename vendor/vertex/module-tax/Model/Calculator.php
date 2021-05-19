<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Vertex\Data\LineItemInterface;
use Vertex\Data\TaxInterface;
use Vertex\Tax\Model\Api\Data\QuotationRequestBuilder;
use Vertex\Tax\Model\Api\Utility\PriceForTax;
use Vertex\Tax\Model\Config\Source\SummarizeTax;
use Vertex\Tax\Model\TaxQuote\TaxQuoteRequest;

/**
 * Vertex Tax Calculator
 */
class Calculator
{
    public const MESSAGE_KEY = 'vertex-messages';
    public const TAX_TYPE_PRINTED_CARD_GW = 'printed_card_gw';
    public const TAX_TYPE_QUOTE_GW = 'quote_gw';
    public const TAX_TYPE_SHIPPING = 'shipping';

    /** @var bool */
    private $addMessageToVertexGroup;

    /** @var AddressDeterminer */
    private $addressDeterminer;

    /** @var AppliedTaxInterfaceFactory */
    private $appliedTaxFactory;

    /** @var AppliedTaxRateInterfaceFactory */
    private $appliedTaxRateFactory;

    /** @var Config */
    private $config;

    /** @var ExceptionLogger */
    private $logger;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /** @var PriceForTax */
    private $priceForTaxCalculation;

    /** @var TaxQuoteRequest */
    private $quoteRequest;

    /** @var QuotationRequestBuilder */
    private $requestFactory;

    /** @var TaxDetailsInterfaceFactory */
    private $taxDetailsFactory;

    /** @var TaxDetailsItemInterfaceFactory */
    private $taxDetailsItemFactory;

    public function __construct(
        TaxDetailsInterfaceFactory $taxDetailsFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemFactory,
        QuotationRequestBuilder $requestFactory,
        TaxQuoteRequest $quoteRequest,
        AppliedTaxInterfaceFactory $appliedTaxFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateFactory,
        PriceCurrencyInterface $priceCurrency,
        ExceptionLogger $logger,
        Config $config,
        ManagerInterface $messageManager,
        PriceForTax $priceForTaxCalculation,
        AddressDeterminer $addressDeterminer,
        bool $addMessageToVertexGroup = true
    ) {
        $this->taxDetailsFactory = $taxDetailsFactory;
        $this->requestFactory = $requestFactory;
        $this->quoteRequest = $quoteRequest;
        $this->taxDetailsItemFactory = $taxDetailsItemFactory;
        $this->appliedTaxFactory = $appliedTaxFactory;
        $this->appliedTaxRateFactory = $appliedTaxRateFactory;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->addMessageToVertexGroup = $addMessageToVertexGroup;
        $this->priceForTaxCalculation = $priceForTaxCalculation;
        $this->addressDeterminer = $addressDeterminer;
    }

    /**
     * Calculate Taxes
     *
     * @param QuoteDetailsInterface $quoteDetails
     * @param string|null $scopeCode
     * @param bool $round
     */
    public function calculateTax(
        QuoteDetailsInterface $quoteDetails,
        $scopeCode,
        bool $round = true
    ): TaxDetailsInterface {
        $items = $quoteDetails->getItems();

        $destination = $this->addressDeterminer->determineDestination(
            $quoteDetails->getShippingAddress(),
            $quoteDetails->getCustomerId()
        );

        if ($destination === null) {
            $administrativeDestination = $this->addressDeterminer->determineAdministrativeDestination(
                $quoteDetails->getBillingAddress(),
                $quoteDetails->getCustomerId()
            );

            // Don't perform calculation when administrativeDestination is null and destination is also null
            if ($administrativeDestination === null) {
                return $this->createEmptyDetails($quoteDetails);
            }
        }

        // Don't perform calculation when there are no items or the only item is shipping
        if (empty($items) || $this->onlyShipping($items)) {
            return $this->createEmptyDetails($quoteDetails);
        }

        try {
            $request = $this->requestFactory->buildFromQuoteDetails($quoteDetails, $scopeCode);
            // Send to Vertex!
            $result = $this->quoteRequest->taxQuote($request, $scopeCode);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $group = $this->addMessageToVertexGroup ? self::MESSAGE_KEY : null;
            // Clear previous Vertex error messages
            $this->messageManager->getMessages(true, $group);
            $this->messageManager->addErrorMessage(
                __('Unable to calculate taxes. This could be caused by an invalid address provided in checkout.'),
                $group
            );
            return $this->createEmptyDetails($quoteDetails);
        }

        /** @var LineItemInterface[] $resultItems */
        $resultItems = [];
        foreach ($result->getLineItems() as $lineItem) {
            $resultItems[$lineItem->getLineItemId()] = $lineItem;
        }

        /** @var TaxDetailsInterface $taxDetails */
        $taxDetails = $this->taxDetailsFactory->create();
        $taxDetails->setSubtotal(0)
            ->setTaxAmount(0)
            ->setAppliedTaxes([]);

        /** @var QuoteDetailsItemInterface[] $processItems Line items we need to process taxes for */
        $processItems = [];
        /** @var QuoteDetailsItemInterface[] $childrenByParent Child line items indexed by parent code */
        $childrenByParent = [];
        /** @var TaxDetailsItemInterface[] $processedItems Processed Line items */
        $processedItems = [];

        /*
         * Here we separate items into top-level and child items.  The children will be processed separately and then
         * added together for the parent item
         */
        foreach ($quoteDetails->getItems() as $item) {
            if ($item->getParentCode()) {
                $childrenByParent[$item->getParentCode()][] = $item;
            } else {
                $processItems[$item->getCode()] = $item;
            }
        }

        foreach ($processItems as $item) {
            if (isset($childrenByParent[$item->getCode()])) { // If this top-level item has child products
                /** @var TaxDetailsItemInterface[] $processedChildren To be used to figure out our top-level details */
                $processedChildren = [];

                // Process the children first, our top-level product will be the combination of them
                foreach ($childrenByParent[$item->getCode()] as $child) {
                    /** @var QuoteDetailsItemInterface $child */

                    $resultItem = $resultItems[$child->getCode()];
                    $processedItem = $resultItem
                        ? $this->createTaxDetailsItem($child, $resultItem, $round, (float)$item->getQuantity())
                        : $this->createEmptyDetailsTaxItem($child);

                    // Add this item's tax information to the quote aggregate
                    $this->aggregateTaxData($taxDetails, $processedItem);

                    $processedItems[$processedItem->getCode()] = $processedItem;
                    $processedChildren[] = $processedItem;
                }
                /** @var TaxDetailsItemInterface $processedItem */
                $processedItem = $this->taxDetailsItemFactory->create();
                $processedItem->setCode($item->getCode())
                    ->setType($item->getType());

                $rowTotal = 0.0;
                $rowTotalInclTax = 0.0;
                $rowTax = 0.0;
                // Combine the totals from the children
                foreach ($processedChildren as $child) {
                    $rowTotal += $child->getRowTotal();
                    $rowTotalInclTax += $child->getRowTotalInclTax();
                    $rowTax += $child->getRowTax();
                }

                $price = $rowTotal / $item->getQuantity();
                $priceInclTax = $rowTotalInclTax / $item->getQuantity();

                $processedItem->setPrice($this->optionalRound($price, $round))
                    ->setPriceInclTax($this->optionalRound($priceInclTax, $round))
                    ->setRowTotal($this->optionalRound($rowTotal, $round))
                    ->setRowTotalInclTax($this->optionalRound($rowTotalInclTax, $round))
                    ->setRowTax($this->optionalRound($rowTax, $round));
            // Aggregation to $taxDetails takes place on the child level
            } else {
                $resultItem = $resultItems[$item->getCode()];
                $processedItem = $resultItem
                    ? $this->createTaxDetailsItem($item, $resultItem, $round)
                    : $this->createEmptyDetailsTaxItem($item);

                $this->aggregateTaxData($taxDetails, $processedItem);
            }

            $processedItems[$item->getCode()] = $processedItem;
        }
        $taxDetails->setItems($processedItems);

        return $taxDetails;
    }

    /**
     * Add tax details from an item to the overall tax details
     */
    private function aggregateTaxData(TaxDetailsInterface $taxDetails, TaxDetailsItemInterface $taxItemDetails): void
    {
        $taxDetails->setSubtotal($taxDetails->getSubtotal() + $taxItemDetails->getRowTotal());
        $taxDetails->setTaxAmount($taxDetails->getTaxAmount() + $taxItemDetails->getRowTax());

        $itemAppliedTaxes = $taxItemDetails->getAppliedTaxes();
        if (empty($itemAppliedTaxes)) {
            return;
        }

        $appliedTaxes = $taxDetails->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $taxId => $itemAppliedTax) {
            if (!isset($appliedTaxes[$taxId])) {
                $rates = [];
                $itemRates = $itemAppliedTax->getRates();
                foreach ($itemRates as $rate) {
                    /** @var AppliedTaxRateInterface $newRate */
                    $newRate = $this->appliedTaxRateFactory->create();
                    $newRate->setPercent($rate->getPercent())
                        ->setTitle($rate->getTitle())
                        ->setCode($rate->getCode());
                    $rates[] = $newRate;
                }

                /** @var AppliedTaxInterface $appliedTax */
                $appliedTax = $this->appliedTaxFactory->create();
                $appliedTax->setPercent($itemAppliedTax->getPercent())
                    ->setAmount($itemAppliedTax->getAmount())
                    ->setTaxRateKey($itemAppliedTax->getTaxRateKey())
                    ->setRates($rates);

                $appliedTaxes[$taxId] = $appliedTax;
            } else {
                $appliedTaxes[$taxId]->setAmount($appliedTaxes[$taxId]->getAmount() + $itemAppliedTax->getAmount());
            }
        }
        $taxDetails->setAppliedTaxes($appliedTaxes);
    }

    /**
     * Format an array of {@see TaxInterface} into applied taxes
     *
     * @param TaxInterface[] $taxes
     * @param string $lineItemId
     * @return AppliedTaxInterface[]
     */
    private function createAppliedTaxes(array $taxes, $lineItemId): array
    {
        $taxDetailType = SummarizeTax::PRODUCT_AND_SHIPPING;
        if ($lineItemId === static::TAX_TYPE_SHIPPING) {
            $taxDetailType = static::TAX_TYPE_SHIPPING;
        } elseif ($lineItemId === static::TAX_TYPE_QUOTE_GW
            || $lineItemId === static::TAX_TYPE_PRINTED_CARD_GW
            || strpos($lineItemId, 'item_gw') === 0) {
            $taxDetailType = static::TAX_TYPE_QUOTE_GW;
        }

        $appliedTaxes = [];
        foreach ($taxes as $tax) {
            $jurisdiction = $tax->getJurisdiction();
            if (!$jurisdiction) {
                continue;
            }
            if ($this->config->getSummarizeTax() === SummarizeTax::JURISDICTION) {
                $taxDetailType = $jurisdiction->getName();
            }

            /** @var AppliedTaxInterface $appliedTax */
            /** @var AppliedTaxRateInterface $rate */
            if (isset($appliedTaxes[$taxDetailType])) {
                $appliedTax = $appliedTaxes[$taxDetailType];
            } else {
                $appliedTax = $this->appliedTaxFactory->create();
                $appliedTax->setAmount(0);
                $appliedTax->setPercent(0);
                $appliedTax->setTaxRateKey($taxDetailType);

                $rate = $this->appliedTaxRateFactory->create();
                $rate->setPercent(0)
                    ->setCode($taxDetailType);

                $rate->setTitle($this->getTaxLabel($taxDetailType));
                $appliedTax->setRates([$rate]);
                $appliedTaxes[$taxDetailType] = $appliedTax;
            }

            $rate = $appliedTax->getRates()[0];
            $rate->setPercent($rate->getPercent() + ($tax->getEffectiveRate() * 100));

            $appliedTax->setAmount($appliedTax->getAmount() + $tax->getAmount());
            $appliedTax->setPercent($appliedTax->getPercent() + ($tax->getEffectiveRate() * 100));
        }

        return $appliedTaxes;
    }

    /**
     * Create an empty {@see TaxDetailsInterface}
     *
     * This method is used to provide Magento the information it expects while
     * avoiding a costly tax calculation when we don't want one (or think it
     * will provide no value)
     */
    private function createEmptyDetails(QuoteDetailsInterface $quoteDetails): TaxDetailsInterface
    {
        /** @var TaxDetailsInterface $details */
        $details = $this->taxDetailsFactory->create();

        $subtotal = 0;
        $items = [];

        foreach ($quoteDetails->getItems() as $quoteItem) {
            $taxItem = $this->createEmptyDetailsTaxItem($quoteItem);
            $subtotal += $taxItem->getRowTotal();
            // Magento has an undocumented assumption that tax detail items are indexed by code
            $items[$taxItem->getCode()] = $taxItem;
        }

        $details->setSubtotal($subtotal)
            ->setTaxAmount(0)
            ->setDiscountTaxCompensationAmount(0)
            ->setAppliedTaxes([])
            ->setItems($items);

        return $details;
    }

    /**
     * Create an empty {@see TaxDetailsItemInterface}
     *
     * This is used by {@see self::createEmptyDetails()}
     */
    private function createEmptyDetailsTaxItem(QuoteDetailsItemInterface $quoteDetailsItem): TaxDetailsItemInterface
    {
        /** @var TaxDetailsItemInterface $taxDetailsItem */
        $taxDetailsItem = $this->taxDetailsItemFactory->create();

        $rowTotal = ($quoteDetailsItem->getUnitPrice() * $quoteDetailsItem->getQuantity());

        $taxDetailsItem->setCode($quoteDetailsItem->getCode())
            ->setType($quoteDetailsItem->getType())
            ->setRowTax(0)
            ->setPrice($quoteDetailsItem->getUnitPrice())
            ->setPriceInclTax($quoteDetailsItem->getUnitPrice())
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotal)
            ->setDiscountTaxCompensationAmount(0)
            ->setDiscountAmount($quoteDetailsItem->getDiscountAmount())
            ->setAssociatedItemCode($quoteDetailsItem->getAssociatedItemCode())
            ->setTaxPercent(0)
            ->setAppliedTaxes([]);

        return $taxDetailsItem;
    }

    /**
     * Create a {@see TaxDetailsItemInterface}
     *
     * Combines information from the {@see QuoteDetailsItemInterface} and resulting {@see LineItemInterface} to assemble
     * a complete {@see TaxDetailsItemInterface}
     */
    private function createTaxDetailsItem(
        QuoteDetailsItemInterface $quoteDetailsItem,
        LineItemInterface $vertexLineItem,
        bool $round = true,
        float $parentQty = 1.0
    ): TaxDetailsItemInterface {
        // Combine the rates of all taxes applicable to the Line Item
        $effectiveRate = array_reduce(
            $vertexLineItem->getTaxes(),
            static function ($result, TaxInterface $tax) {
                return $result + $tax->getEffectiveRate();
            },
            0
        );

        // Vertex QTY includes parent item
        $perItemTax = $vertexLineItem->getTotalTax() / $vertexLineItem->getQuantity();
        $unitPrice = $quoteDetailsItem->getUnitPrice();
        $extendedPrice = $this->priceForTaxCalculation->getOriginalItemPriceOnQuote(
            $quoteDetailsItem,
            $unitPrice,
            $parentQty
        );

        /** @var TaxDetailsItemInterface $taxDetailsItem */
        $taxDetailsItem = $this->taxDetailsItemFactory->create();

        $taxDetailsItem->setCode($vertexLineItem->getLineItemId())
            ->setType($quoteDetailsItem->getType())
            ->setRowTax($this->optionalRound($vertexLineItem->getTotalTax(), $round))
            ->setPrice($this->optionalRound($unitPrice, $round))
            ->setPriceInclTax($this->optionalRound($unitPrice + $perItemTax, $round))
            ->setRowTotal($this->optionalRound($extendedPrice, $round))
            ->setRowTotalInclTax($this->optionalRound($extendedPrice + $vertexLineItem->getTotalTax(), $round))
            ->setDiscountTaxCompensationAmount(0)
            ->setAssociatedItemCode($quoteDetailsItem->getAssociatedItemCode())
            ->setTaxPercent($effectiveRate * 100)
            ->setAppliedTaxes(
                $this->createAppliedTaxes(
                    $vertexLineItem->getTaxes(),
                    $vertexLineItem->getLineItemId()
                )
            );

        return $taxDetailsItem;
    }

    /**
     * Retrieve tax label
     *
     * @param string $code
     * @return string
     */
    private function getTaxLabel($code): string
    {
        switch ($code) {
            case SummarizeTax::PRODUCT_AND_SHIPPING:
                return __('Sales and Use')->render();

            case static::TAX_TYPE_QUOTE_GW:
            case static::TAX_TYPE_PRINTED_CARD_GW:
                return __('Gift Options')->render();

            case static::TAX_TYPE_SHIPPING:
                return __('Shipping')->render();
        }

        return $code;
    }

    /**
     * Determine if an array of QuoteDetailsItemInterface contains only shipping entries
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return bool
     */
    private function onlyShipping(array $items): bool
    {
        foreach ($items as $item) {
            if ($item->getCode() !== 'shipping') {
                return false;
            }
        }

        return true;
    }

    /**
     * Round a number
     *
     * @param number $number
     * @param bool $round
     * @return float
     */
    private function optionalRound($number, $round = true)
    {
        return $round ? $this->priceCurrency->round($number) : $number;
    }
}
