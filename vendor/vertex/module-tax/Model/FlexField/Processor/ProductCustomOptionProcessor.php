<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField\Processor;

use DateTimeImmutable;
use Exception;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Psr\Log\LoggerInterface;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;
use Vertex\Tax\Model\Repository\CustomOptionFlexibleFieldRepository;

/**
 * Flexible Field Processor to retrieve the values of Product Custom Options
 */
class ProductCustomOptionProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var CartItemOptionsProcessor */
    private $cartItemsProcessor;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var ProductCustomOptionRepositoryInterface */
    private $customOptionRepository;

    /** @var ExceptionLogger */
    private $exceptionLogger;

    /** @var LoggerInterface */
    private $logger;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CustomOptionFlexibleFieldRepository */
    private $repository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param CustomOptionFlexibleFieldRepository $repository
     * @param StoreManagerInterface $storeManager
     * @param ProductCustomOptionRepositoryInterface $customOptionRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemOptionsProcessor $cartItemsProcessor
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     * @param ExceptionLogger $exceptionLogger
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        FlexFieldProcessableAttributeFactory $attributeFactory,
        CustomOptionFlexibleFieldRepository $repository,
        StoreManagerInterface $storeManager,
        ProductCustomOptionRepositoryInterface $customOptionRepository,
        ProductRepositoryInterface $productRepository,
        CartItemOptionsProcessor $cartItemsProcessor,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger,
        ExceptionLogger $exceptionLogger
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->attributeFactory = $attributeFactory;
        $this->repository = $repository;
        $this->storeManager = $storeManager;
        $this->customOptionRepository = $customOptionRepository;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->cartItemsProcessor = $cartItemsProcessor;
        $this->cartRepository = $cartRepository;
        $this->exceptionLogger = $exceptionLogger;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return array_reduce(
            [
                FlexFieldProcessableAttribute::TYPE_DATE,
                FlexFieldProcessableAttribute::TYPE_CODE
            ],
            function (array $carry, $type) {
                /** @var FlexFieldProcessableAttribute $attribute */
                $attribute = $this->attributeFactory->create();

                $attribute->setProcessor(static::class);
                $attribute->setType($type);
                $attribute->setOptionGroup('Product');
                $attribute->setLabel('Custom Option (customized on product level)');
                $attribute->setAttributeCode('product_custom_option.' . $type);

                $carry['product_custom_option.' . $type] = $attribute;
                return $carry;
            },
            []
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $options = $this->getCustomOptionsFromCreditMemoItem($item);
        return $this->getProductOptionValue(
            $this->getSkuFromProductId($item->getProductId()),
            $options,
            $this->getStoreIdFromCreditMemoItem($item),
            $fieldType,
            $fieldId
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $options = $this->getCustomOptionsFromInvoiceItem($item);
        return $this->getProductOptionValue(
            $this->getSkuFromProductId($item->getProductId()),
            $options,
            $this->getStoreIdFromInvoiceItem($item),
            $fieldType,
            $fieldId
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        $options = $this->getCustomOptionsFromOrderItem($item);
        return $this->getProductOptionValue(
            $this->getSkuFromProductId($item->getProductId()),
            $options,
            $item->getStoreId(),
            $fieldType,
            $fieldId
        );
    }

    /**
     * @inheritDoc
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes || !$extAttributes->getQuoteItemId()) {
            return null;
        }

        $cartItem = $this->getCartItem($extAttributes->getQuoteItemId(), $extAttributes->getQuoteId());

        if ($cartItem === null) {
            return null;
        }

        $storeId = $item->getExtensionAttributes()->getStoreId();
        $options = $this->getCustomOptionsFromCartItem($cartItem);
        return $this->getProductOptionValue(
            $this->getSkuFromProductId($item->getExtensionAttributes()->getProductId()),
            $options,
            $storeId,
            $fieldType,
            $fieldId
        );
    }

    /**
     * Retrieve a Cart Item
     *
     * We create a dependency on an internal class in magento/module-quote to process the custom options and
     * retrieve the cart item.
     *
     * @param int $cartItemId
     * @param int $cartId
     * @return CartItemInterface|null
     * @composerDependency magento/module-quote >=100.1.0 <=100.1.11 || >=101.0.0 <=101.0.8 || >=101.1.0 <= 101.1.2
     */
    private function getCartItem($cartItemId, $cartId)
    {
        try {
            $cart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning("Could not locate cart with ID {$cartId} to process for flexible fields");
            $this->exceptionLogger->warning($e);
            return null;
        }

        $cartItems = $cart instanceof Quote ? $cart->getAllVisibleItems() : $cart->getItems();

        $cartItem = array_reduce(
            $cartItems,
            static function ($carry, $cartItem) use ($cartItemId) {
                /** @var Quote\Item|CartItemInterface $cartItem */
                if ($cartItem->getItemId() === $cartItemId) {
                    return $cartItem;
                }
                return $carry;
            }
        );

        // This is where our weird composer dependency on module-quote comes from.  Ideally this method would just be
        // asking the CartItemRepository to supply us the items for the cart, and then we'd loop through and grab ours
        // and everything would be loaded and great.  HOWEVER, we can't do this.  When an order is being created in the
        // admin panel the quote is not active.  And when the quote is not active the CartItemRepository will refuse to
        // return the cart items.
        // See: https://github.com/magento/magento2/issues/19846

        if ($cartItem->getSku()) {
            $cartItem = $this->cartItemsProcessor->addProductOptions($cartItem->getProductType(), $cartItem);
            $cartItem = $this->cartItemsProcessor->applyCustomOptions($cartItem);
            return $cartItem;
        }
        return null;
    }

    /**
     * Retrieve all custom options for a CartItem
     *
     * @param CartItemInterface $cartItem
     * @return CustomOptionInterface[]
     */
    private function getCustomOptionsFromCartItem(CartItemInterface $cartItem): array
    {
        return $cartItem->getProductOption() !== null
        && $cartItem->getProductOption()->getExtensionAttributes() !== null
        && $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions() !== null
            ? $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions()
            : [];
    }

    /**
     * Retrieve all custom options for a CreditMemoItem
     *
     * @param CreditmemoItemInterface $creditmemoItem
     * @return CustomOptionInterface[]
     */
    private function getCustomOptionsFromCreditMemoItem(CreditmemoItemInterface $creditmemoItem): array
    {
        $orderItem = $this->orderItemRepository->get($creditmemoItem->getOrderItemId());
        return $this->getCustomOptionsFromOrderItem($orderItem);
    }

    /**
     * Retrieve all custom options for an InvoiceItem
     *
     * @param InvoiceItemInterface $invoiceItem
     * @return CustomOptionInterface[]
     */
    private function getCustomOptionsFromInvoiceItem(InvoiceItemInterface $invoiceItem): array
    {
        $orderItem = $this->orderItemRepository->get($invoiceItem->getOrderItemId());
        return $this->getCustomOptionsFromOrderItem($orderItem);
    }

    /**
     * Retrieve all custom options for an OrderItem
     *
     * @param OrderItemInterface $orderItem
     * @return CustomOptionInterface[]
     */
    private function getCustomOptionsFromOrderItem(OrderItemInterface $orderItem): array
    {
        return $orderItem->getProductOption() !== null
        && $orderItem->getProductOption()->getExtensionAttributes() !== null
        && $orderItem->getProductOption()->getExtensionAttributes()->getCustomOptions() !== null
            ? $orderItem->getProductOption()->getExtensionAttributes()->getCustomOptions()
            : [];
    }

    /**
     * Retrieve the value of a Custom Option
     *
     * @param string $productSku
     * @param CustomOptionInterface[] $options
     * @param string|int $storeId
     * @param string $fieldType
     * @param string|int $fieldId
     * @return mixed
     */
    private function getProductOptionValue($productSku, $options, $storeId, $fieldType, $fieldId)
    {
        try {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        } catch (NoSuchEntityException $e) {
            $this->logger->warning(
                "Could not locate the store record for {$storeId} in " . __FILE__ . ' in method getProductOptionValue '
            );
            $websiteId = 0;
        }

        $optionIds = array_map(
            static function (CustomOptionInterface $option) {
                return $option->getOptionId();
            },
            $options
        );

        $mapping = $this->repository->getListForOptions($optionIds, $websiteId);
        $fieldId = "{$fieldType}.{$fieldId}";

        foreach ($options as $option) {
            $optionId = $option->getOptionId();
            if (isset($mapping[$optionId]) && $mapping[$optionId]->getFlexFieldId() === $fieldId) {
                $optionValue = $option->getOptionValue();
                if ($this->isDate($productSku, $option)) {
                    $optionValuePieces = explode(',', $optionValue);
                    $optionValue = end($optionValuePieces);
                }
                if ($fieldType === 'date') {
                    try {
                        return new DateTimeImmutable($optionValue);
                    } catch (Exception $e) {
                        $this->logger->warning(
                            'Custom option date value of ' . $optionValue
                            . ' could not be converted to DateTimeImmutable in ' . __FILE__
                        );
                        return null;
                    }
                }
                if ($this->isSelect($productSku, $option)) {
                    return $this->getSelectValue($productSku, $option);
                }
                return $optionValue;
            }
        }

        return null;
    }

    /**
     * Retrieve the title of the selected value for a custom option
     *
     * @param string $productSku
     * @param CustomOptionInterface $customOption
     * @return string|null
     */
    private function getSelectValue($productSku, CustomOptionInterface $customOption)
    {
        if ($productSku === null) {
            return null;
        }
        try {
            $productCustomOption = $this->customOptionRepository->get($productSku, $customOption->getOptionId());
        } catch (NoSuchEntityException $exception) {
            $this->logger->warning(
                'Could not find Option ID ' . $customOption->getOptionId() . ' for product ' . $productSku
                . ' in ' . __FILE__
            );
            return null;
        }
        $values = $productCustomOption->getValues();
        foreach ($values as $value) {
            if ($value->getOptionTypeId() === $customOption->getOptionValue()) {
                return $value->getTitle();
            }
        }
        return null;
    }

    /**
     * Determine the SKU for a given product ID
     *
     * @param int $productId
     * @return string|null
     */
    private function getSkuFromProductId($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $product->getSku();
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('Could not find SKU for Product with ID ' . $productId . ' in ' . __FILE__);
            return null;
        }
    }

    /**
     * Retrieve the Store ID from a CreditMemo Item
     *
     * @param CreditmemoItemInterface $creditmemoItem
     * @return int|null
     */
    private function getStoreIdFromCreditMemoItem(CreditmemoItemInterface $creditmemoItem)
    {
        $orderItem = $this->orderItemRepository->get($creditmemoItem->getOrderItemId());
        return $orderItem->getStoreId();
    }

    /**
     * Retrieve the Store ID from an Invoice Item
     *
     * @param InvoiceItemInterface $invoiceItem
     * @return int|null
     */
    private function getStoreIdFromInvoiceItem(InvoiceItemInterface $invoiceItem)
    {
        $orderItem = $this->orderItemRepository->get($invoiceItem->getOrderItemId());
        return $orderItem->getStoreId();
    }

    /**
     * Determine if a custom option is a date
     *
     * @param string $productSku
     * @param CustomOptionInterface $customOption
     * @return bool
     */
    private function isDate($productSku, CustomOptionInterface $customOption): bool
    {
        $productCustomOption = $this->customOptionRepository->get($productSku, $customOption->getOptionId());
        return in_array(
            $productCustomOption->getType(),
            [
                ProductCustomOptionInterface::OPTION_TYPE_DATE,
                ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME,
                ProductCustomOptionInterface::OPTION_TYPE_TIME
            ],
            true
        );
    }

    /**
     * Determine if a custom option is a selection
     *
     * @param string $productSku
     * @param CustomOptionInterface $customOption
     * @return bool
     */
    private function isSelect($productSku, CustomOptionInterface $customOption): bool
    {
        $productCustomOption = $this->customOptionRepository->get($productSku, $customOption->getOptionId());
        return in_array(
            $productCustomOption->getType(),
            [
                ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                ProductCustomOptionInterface::OPTION_TYPE_RADIO
            ],
            true
        );
    }
}
