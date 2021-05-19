<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Data\FlexFieldBuilder;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Api\Utility\PriceForTax;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Processes Items on an Invoice and converts them to an array of LineItemInterface
 */
class InvoiceItemProcessor implements InvoiceProcessorInterface
{
    /** @var FlexFieldBuilder */
    private $flexFieldBuilder;

    /** @var ItemProcessor */
    private $itemProcessor;

    /** @var LineItemInterfaceFactory */
    private $lineItemFactory;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var PriceForTax */
    private $priceForTaxCalculation;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    public function __construct(
        ItemProcessor $itemProcessor,
        LineItemInterfaceFactory $lineItemFactory,
        TaxClassNameRepository $taxClassNameRepository,
        FlexFieldBuilder $flexFieldBuilder,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        PriceForTax $priceForTaxCalculation
    ) {
        $this->itemProcessor = $itemProcessor;
        $this->lineItemFactory = $lineItemFactory;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->flexFieldBuilder = $flexFieldBuilder;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->priceForTaxCalculation = $priceForTaxCalculation;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, InvoiceInterface $invoice)
    {
        /** @var InvoiceItemInterface[] $invoiceItems All InvoiceItems indexed by id */
        $invoiceItems = [];

        /** @var int[] $productIds All Product IDs in the invoice */
        $productSku = [];

        /** @var LineItemInterface[] $lineItems Vertex SDK LineItems to be returned */
        $lineItems = [];

        // Build the invoiceItems, parentItemIds, and productIds arrays

        foreach ($invoice->getItems() as $item) {
            if ($item->getBaseRowTotal() === null) {
                continue;
            }
            $invoiceItems[$item->getOrderItemId()] = $item;
            $productSku[] = $item->getSku();
        }

        $orderId = $invoice->getOrderId();
        $products = $this->itemProcessor->getProductsIndexedBySku($productSku, $orderId);

        /** @var int[] $taxClasses Key is InvoiceItem ID, Value is Tax Class ID */
        $taxClasses = [];

        $storeId = $invoice->getStoreId();

        $lineItemMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $storeId);

        foreach ($invoiceItems as $item) {
            $product = $products[$item->getProductId()] ?? false;
            $taxClassAttribute = $product ? $product->getCustomAttribute('tax_class_id') : false;
            $taxClassId = $taxClassAttribute ? $taxClassAttribute->getValue() : 0;

            if ($item->getBaseRowTotal() === null) {
                // For bundle products, the parent has a row total of NULL
                continue;
            }

            /** @var OrderItemInterface $orderItem */
            $orderItem = $item->getOrderItem();

            /** @var LineItemInterface $lineItem */
            $lineItem = $this->lineItemFactory->create();
            $lineItem->setProductCode(
                $this->stringUtilities->substr($item->getSku(), 0, $lineItemMapper->getProductCodeMaxLength())
            );

            $baseItemPrice = $item->getBasePrice();
            $basePriceOriginal = $this->priceForTaxCalculation->getPriceForTaxCalculationFromOrderItem(
                $orderItem,
                $baseItemPrice
            );
            $extendedPrice = ($basePriceOriginal * $item->getQty()) - $item->getBaseDiscountAmount();

            $lineItem->setQuantity($item->getQty());
            $lineItem->setUnitPrice($baseItemPrice);
            $lineItem->setExtendedPrice($extendedPrice);
            $lineItem->setLineItemId($item->getOrderItemId());

            $taxClasses[$item->getOrderItemId()] = $taxClassId;

            $lineItem->setFlexibleFields($this->flexFieldBuilder->buildAllFromInvoiceItem($item, $storeId));

            $commodityCode = $orderItem->getExtensionAttributes()->getVertexCommodityCode();
            if ($commodityCode) {
                $lineItem->setCommodityCode($commodityCode->getCode());
                $lineItem->setCommodityCodeType($commodityCode->getType());
            }

            $lineItems[] = $lineItem;
        }

        /** @var string[int] $taxClassNames Tax Classes indexed by ID */
        $taxClassNames = $this->taxClassNameRepository->getListByIds(array_values($taxClasses));

        foreach ($lineItems as $lineItem) {
            $lineItemId = $lineItem->getLineItemId();
            $taxClass = $taxClasses[$lineItemId];
            $taxClassName = $taxClassNames[$taxClass];
            $lineItem->setProductClass(
                $this->stringUtilities->substr($taxClassName, 0, $lineItemMapper->getProductTaxClassNameMaxLength())
            );
        }

        $request->setLineItems(array_merge($request->getLineItems(), $lineItems));

        return $request;
    }
}
