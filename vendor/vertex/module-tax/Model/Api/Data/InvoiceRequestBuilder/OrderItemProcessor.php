<?php

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Vertex\Data\CustomerInterface;
use Vertex\Data\LineItemInterface;
use Vertex\Data\LineItemInterfaceFactory;
use Vertex\Services\Invoice\RequestInterface;
use Vertex\Tax\Model\Api\Data\CustomerBuilder;
use Vertex\Tax\Model\Api\Data\FlexFieldBuilder;
use Vertex\Tax\Model\Api\Utility\IsVirtualLineItemDeterminer;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Processes Items on an Order and adds them to a Vertex Invoice's LineItems
 */
class OrderItemProcessor implements OrderProcessorInterface
{
    /** @var TaxClassNameRepository */
    private $classNameRepository;

    /** @var SearchCriteriaBuilderFactory */
    private $criteriaBuilderFactory;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var FlexFieldBuilder */
    private $flexFieldBuilder;

    /** @var LineItemInterfaceFactory */
    private $lineItemFactory;

    /** @var ExceptionLogger */
    private $logger;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var OrderAddressRepositoryInterface */
    private $orderAddressRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var IsVirtualLineItemDeterminer */
    private $virtualLineItemDeterminer;

    /**
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param TaxClassNameRepository $classNameRepository
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     * @param FlexFieldBuilder $flexFieldBuilder
     * @param IsVirtualLineItemDeterminer $virtualLineItemDeterminer
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param CustomerBuilder $customerBuilder
     * @param ExceptionLogger $logger
     */
    public function __construct(
        LineItemInterfaceFactory $lineItemFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        TaxClassNameRepository $classNameRepository,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        FlexFieldBuilder $flexFieldBuilder,
        IsVirtualLineItemDeterminer $virtualLineItemDeterminer,
        OrderAddressRepositoryInterface $orderAddressRepository,
        CustomerBuilder $customerBuilder,
        ExceptionLogger $logger
    ) {
        $this->lineItemFactory = $lineItemFactory;
        $this->productRepository = $productRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->classNameRepository = $classNameRepository;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->flexFieldBuilder = $flexFieldBuilder;
        $this->virtualLineItemDeterminer = $virtualLineItemDeterminer;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->customerBuilder = $customerBuilder;
        $this->logger = $logger;
    }

    /**
     * Return if order item can be processed and create line items
     *
     * @param OrderItemInterface $item
     * @return bool
     */
    public function canProcessItem(OrderItemInterface $item): bool
    {
        // Configurables are handled on the child level with getParentItem for pricing data
        if ($item->getProductType() === 'configurable') {
            return false;
        }

        $productType = $item->getParentItem()
            ? $item->getParentItem()->getProductType()
            : $item->getProductType();

        // Dynamic price bundles are handled on the child level and fixed price bundles are handled on the parent level
        if ($productType === 'bundle') {
            return $item->getParentItem()
                ? $this->isBundleItemDynamic($item->getParentItem())
                : !$this->isBundleItemDynamic($item);

        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, OrderInterface $order)
    {
        $lineItems = [];

        $orderItems = [];
        $productIds = [];

        /** @var int[] $taxClasses Key is OrderItem ID, Value is Tax Class ID */
        $taxClasses = [];

        /** @var int|null $storeId */
        $storeId = $order->getStoreId();

        $lineItemMapper = $this->mapperFactory->getForClass(LineItemInterface::class, $storeId);

        foreach ($order->getItems() as $item) {
            if ($item->getBaseRowTotal() === null) {
                continue;
            }
            $orderItems[$item->getItemId()] = $item;
            $productIds[] = $item->getProductId();
        }

        $products = $this->getProductsIndexedById($productIds);

        foreach ($orderItems as $item) {
            if (!$this->canProcessItem($item)) {
                continue;
            }

            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getProductType() === 'configurable') {
                // The child of a configurable does not have the pricing information
                $unitPrice = $parentItem->getBasePrice();
                $extendedPrice = $parentItem->getBaseRowTotal() - $parentItem->getBaseDiscountAmount();
            } else {
                $unitPrice = $item->getBasePrice();
                $extendedPrice = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
            }

            $product = $products[$item->getProductId()];
            $taxClassAttribute = $product->getCustomAttribute('tax_class_id');
            $taxClassId = $taxClassAttribute ? $taxClassAttribute->getValue() : 0;
            $taxClasses[$item->getItemId()] = $taxClassId;

            $lineItem = $this->lineItemFactory->create();
            $lineItem->setProductCode(
                $this->stringUtilities->substr($item->getSku(), 0, $lineItemMapper->getProductCodeMaxLength())
            );
            $lineItem->setQuantity($item->getQtyOrdered());
            $lineItem->setUnitPrice($unitPrice);
            $lineItem->setExtendedPrice($extendedPrice);
            $lineItem->setLineItemId($item->getItemId());

            if ($this->virtualLineItemDeterminer->isOrderItemVirtual($item)
                && $customer = $this->buildCustomerWithBillingAddress($order)
            ) {
                $lineItem->setCustomer($customer);
            }

            $lineItem->setFlexibleFields($this->flexFieldBuilder->buildAllFromOrderItem($item, $storeId));
            $lineItems[] = $lineItem;
        }

        /** @var string[int] $taxClassNames Tax Classes indexed by ID */
        $taxClassNames = $this->classNameRepository->getListByIds(array_values($taxClasses));

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
     * @param OrderInterface $order
     * @return null|CustomerInterface
     */
    private function buildCustomerWithBillingAddress(OrderInterface $order)
    {
        try {
            $billingAddress = $this->orderAddressRepository->get($order->getBillingAddressId());
            return $this->customerBuilder->buildFromOrderAddress($billingAddress);
        } catch (Exception $e) {
            $this->logger->warning($e);
            return null;
        }
    }

    /**
     * Retrieve an array of products indexed by their ID
     *
     * @param int[] $productIds
     * @return ProductInterface[] Indexed by id
     */
    private function getProductsIndexedById(array $productIds)
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter('entity_id', $productIds, 'in');
        $criteria = $criteriaBuilder->create();

        $items = $this->productRepository->getList($criteria)->getItems();

        /** @var ProductInterface[] $products */
        return array_reduce(
            $items,
            static function (array $carry, ProductInterface $product) {
                // This ensures that all products are indexed by ID, it is not an API guarantee
                $carry[$product->getId()] = $product;
                return $carry;
            },
            []
        );
    }

    /**
     * Return if bundle item has dynamic pricing
     *
     * @param OrderItemInterface $item
     * @return bool
     */
    private function isBundleItemDynamic(OrderItemInterface $item): bool
    {
        $childrenItems = [];
        if ($item instanceof Item) {
            $childrenItems = $item->getChildrenItems();
        }

        /** @var OrderItemInterface $child */
        foreach ($childrenItems as $child) {
            if ((float)$child->getBasePrice() > 0) {
                return true;
            }
        }
        return false;
    }
}
