<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

class CreditmemoItemFixedPriceProcessor implements CreditmemoProcessorInterface
{
    /** @var Config */
    private $config;

    /** @var FixedPriceProcessor */
    private $fixedPriceProcessor;

    /** @var ItemProcessor */
    private $itemProcessor;

    /** @var LineItemInterfaceFactory */
    private $lineItemFactory;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    public function __construct(
        ItemProcessor $itemProcessor,
        LineItemInterfaceFactory $lineItemFactory,
        TaxClassNameRepository $taxClassNameRepository,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        FixedPriceProcessor $fixedPriceProcessor,
        Config $config
    ) {
        $this->itemProcessor = $itemProcessor;
        $this->lineItemFactory = $lineItemFactory;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->fixedPriceProcessor = $fixedPriceProcessor;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, CreditmemoInterface $creditmemo): RequestInterface
    {
        /** @var CreditmemoItemInterface[] $memoItems All InvoiceItems indexed by id */
        $memoItems = [];

        /** @var int[] $productIds All Product IDs in the creditmemo */
        $productSku = [];

        /** @var LineItemInterface[] $lineItems Vertex SDK LineItems to be returned */
        $lineItems = [];

        foreach ($creditmemo->getItems() as $item) {
            if ($item->getBaseRowTotal() === null) {
                continue;
            }
            $memoItems[$item->getOrderItemId()] = $item;
            $productSku[] = $item->getSku();
        }

        $orderId = $creditmemo->getOrderId();
        $products = $this->itemProcessor->getProductsIndexedBySku($productSku, $orderId);

        /** @var int[] $taxClasses Key is InvoiceItem ID, Value is Tax Class ID */
        $taxClasses = [];

        $storeId = $creditmemo->getStoreId();

        $lineItemMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $storeId);

        foreach ($memoItems as $item) {
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

            $fixedProductPriceTax = $this->fixedPriceProcessor->creditMemoItemFixedProductTax($item);
            $fixedProductPriceTaxRow = $this->fixedPriceProcessor->creditMemoItemFixedProductTaxRow($item);

            $lineItem->setQuantity($item->getQty());
            $lineItem->setUnitPrice(-1 * $fixedProductPriceTax);
            $lineItem->setExtendedPrice(-1 * $fixedProductPriceTaxRow);
            $lineItem->setLineItemId($item->getOrderItemId());

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

        return (int)$taxClassId;
    }
}
