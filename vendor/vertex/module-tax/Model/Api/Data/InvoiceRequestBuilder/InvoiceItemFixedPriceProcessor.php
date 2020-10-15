<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vertex\Data\CustomerInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Data\CustomerBuilder;
use Vertex\Tax\Model\Api\Utility\IsVirtualLineItemDeterminer;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

class InvoiceItemFixedPriceProcessor implements InvoiceProcessorInterface
{
    /** @var ItemProcessor */
    private $itemProcessor;

    /** @var LineItemInterfaceFactory */
    private $lineItemFactory;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var FixedPriceProcessor  */
    private $fixedPriceProcessor;

    /** @var Config */
    private $config;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderAddressRepositoryInterface */
    private $orderAddressRepository;

    /** @var IsVirtualLineItemDeterminer */
    private $virtualLineItemDeterminer;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        ItemProcessor $itemProcessor,
        LineItemInterfaceFactory $lineItemFactory,
        TaxClassNameRepository $taxClassNameRepository,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        FixedPriceProcessor $fixedPriceProcessor,
        Config $config,
        CustomerBuilder $customerBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderAddressRepositoryInterface $orderAddressRepository,
        IsVirtualLineItemDeterminer $virtualLineItemDeterminer,
        ExceptionLogger $logger
    ) {
        $this->itemProcessor = $itemProcessor;
        $this->lineItemFactory = $lineItemFactory;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->fixedPriceProcessor = $fixedPriceProcessor;
        $this->config = $config;
        $this->customerBuilder = $customerBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->virtualLineItemDeterminer = $virtualLineItemDeterminer;
        $this->logger = $logger;
        $this->virtualLineItemDeterminer = $virtualLineItemDeterminer;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, InvoiceInterface $invoice) :RequestInterface
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
            $taxClassId = $product ? $this->getFptTaxClassByProduct($product) : 0;

            if ($item->getBaseRowTotal() === null) {
                // For bundle products, the parent has a row total of NULL
                continue;
            }

            /** @var LineItemInterface $lineItem */
            $lineItem = $this->lineItemFactory->create();

            $lineItem->setProductCode(
                $this->stringUtilities->substr(
                    $this->config->getItemPrefixCodeForFixedProductTax($storeId) . $item->getSku(),
                    0,
                    $lineItemMapper->getProductCodeMaxLength()
                )
            );

            $fixedProductPriceTax = $this->fixedPriceProcessor->invoiceItemFixedProductTax($item);
            $fixedProductPriceTaxRow = $this->fixedPriceProcessor->invoiceItemFixedProductTaxRow($item);

            $lineItem->setQuantity($item->getQty());
            $lineItem->setUnitPrice($fixedProductPriceTax);
            $lineItem->setExtendedPrice($fixedProductPriceTaxRow);
            $lineItem->setLineItemId($item->getOrderItemId());

            if ($this->virtualLineItemDeterminer->isInvoiceItemVirtual($item)
                && $customer = $this->buildCustomerWithBillingAddress($orderId)
            ) {
                $lineItem->setCustomer($customer);
            }

            $taxClasses[$item->getOrderItemId()] = $taxClassId;

            if ($lineItem->getExtendedPrice() == 0) {
                continue;
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
                $this->stringUtilities->substr(
                    $taxClassName,
                    0,
                    $lineItemMapper->getProductTaxClassNameMaxLength()
                )
            );
        }

        $request->setLineItems(array_merge($request->getLineItems(), $lineItems));

        return $request;
    }

    private function buildCustomerWithBillingAddress($orderId):? CustomerInterface
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $billingAddress = $this->orderAddressRepository->get($order->getBillingAddressId());
            return $this->customerBuilder->buildFromOrderAddress($billingAddress);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return null;
        }
    }

    private function getFptTaxClassByProduct(Product $product): int
    {
        $taxClassAttribute = $product ? $product->getCustomAttribute('tax_class_id') : false;
        $taxClassId = $taxClassAttribute ? $taxClassAttribute->getValue() : 0;

        $config = $this->config;

        if ($config->isFixedProductTaxEnabled()
            && $config->isFixedProductTaxTaxable()
            && $config->isVertexFixedProductTaxCustom()
            && $config->vertexTaxClassUsedForFixedProductTax()
        ) {
            $taxClassId = $config->vertexTaxClassUsedForFixedProductTax();
        }

        return (int) $taxClassId;
    }
}
