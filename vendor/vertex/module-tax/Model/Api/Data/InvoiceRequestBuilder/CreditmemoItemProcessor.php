<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vertex\Data\CustomerInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Data\CustomerBuilder;
use Vertex\Tax\Model\Api\Data\FlexFieldBuilder;
use Vertex\Tax\Model\Api\Utility\IsVirtualLineItemDeterminer;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Api\Utility\PriceForTax;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Processes Items on a Creditmemo and converts them to an array of LineItemInterface
 */
class CreditmemoItemProcessor implements CreditmemoProcessorInterface
{
    /** @var FlexFieldBuilder */
    private $flexFieldBuilder;

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

    /** @var PriceForTax */
    private $priceForTaxCalculation;

    /** @var IsVirtualLineItemDeterminer */
    private $virtualLineItemDeterminer;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderAddressRepositoryInterface */
    private $orderAddressRepository;

    /** @var ExceptionLogger */
    private $logger;

    /**
     * @param ItemProcessor $itemProcessor
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param TaxClassNameRepository $taxClassNameRepository
     * @param FlexFieldBuilder $flexFieldBuilder
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     * @param PriceForTax $priceForTaxCalculation
     * @param IsVirtualLineItemDeterminer $virtualLineItemDeterminer
     * @param CustomerBuilder $customerBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param ExceptionLogger $logger
     */
    public function __construct(
        ItemProcessor $itemProcessor,
        LineItemInterfaceFactory $lineItemFactory,
        TaxClassNameRepository $taxClassNameRepository,
        FlexFieldBuilder $flexFieldBuilder,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        PriceForTax $priceForTaxCalculation,
        IsVirtualLineItemDeterminer $virtualLineItemDeterminer,
        CustomerBuilder $customerBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderAddressRepositoryInterface $orderAddressRepository,
        ExceptionLogger $logger
    ) {
        $this->itemProcessor = $itemProcessor;
        $this->lineItemFactory = $lineItemFactory;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->flexFieldBuilder = $flexFieldBuilder;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->priceForTaxCalculation = $priceForTaxCalculation;
        $this->virtualLineItemDeterminer = $virtualLineItemDeterminer;
        $this->customerBuilder = $customerBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, CreditmemoInterface $creditmemo)
    {
        /** @var CreditmemoItemInterface[] $memoItems All Creditmemo items indexed by id */
        $memoItems = [];

        /** @var int[] $productIds All Product IDs in the invoice */
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

        $storeId = $creditmemo->getStoreId();

        $lineItemMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $storeId);

        /** @var int[] $taxClasses Key is Creditmemo Item's Order Item ID, Value is Tax Class ID */
        $taxClasses = [];

        foreach ($memoItems as $item) {
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
            $lineItem->setUnitPrice(-1 * $baseItemPrice);
            $lineItem->setExtendedPrice(-1 * $extendedPrice);
            $lineItem->setLineItemId($item->getOrderItemId());

            if ($this->virtualLineItemDeterminer->isCreditMemoItemVirtual($item)
                && $customer = $this->buildCustomerWithBillingAddress($orderId)
            ) {
                $lineItem->setCustomer($customer);
            }

            $taxClasses[$item->getOrderItemId()] = $taxClassId;

            $lineItem->setFlexibleFields($this->flexFieldBuilder->buildAllFromCreditMemoItem($item, $storeId));
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

    /**
     * Build a customer from order billing address
     *
     * @param int $orderId
     * @return null|CustomerInterface
     */
    private function buildCustomerWithBillingAddress($orderId)
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
}
