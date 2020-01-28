<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Temando\Shipping\Model\Config\ModuleConfigInterface;
use Temando\Shipping\Model\Order\ProductAttribute\OrderItemAttributeReader;
use Temando\Shipping\Model\Order\ProductAttribute\ProductAttributeMapper;
use Temando\Shipping\Model\Order\ProductAttribute\QuoteItemAttributeReader;

/**
 * Temando Order Item Builder
 *
 * Create an order item entity to be shared between shipping module and Temando platform.
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderItemInterfaceBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var QuoteItemAttributeReader
     */
    private $quoteItemAttributeReader;

    /**
     * @var OrderItemAttributeReader
     */
    private $orderItemAttributeReader;

    /**
     * @var productAttributeMapper
     */
    private $productAttributeMapper;

    /**
     * OrderItemInterfaceBuilder constructor.
     *
     * @param ObjectFactory $objectFactory
     * @param ModuleConfigInterface $config
     * @param QuoteItemAttributeReader $quoteItemAttributeReader
     * @param OrderItemAttributeReader $orderItemAttributeReader
     * @param productAttributeMapper $productAttributeMapper
     */
    public function __construct(
        ObjectFactory $objectFactory,
        ModuleConfigInterface $config,
        QuoteItemAttributeReader $quoteItemAttributeReader,
        OrderItemAttributeReader $orderItemAttributeReader,
        productAttributeMapper $productAttributeMapper
    ) {
        $this->config = $config;
        $this->quoteItemAttributeReader = $quoteItemAttributeReader;
        $this->orderItemAttributeReader = $orderItemAttributeReader;
        $this->productAttributeMapper = $productAttributeMapper;

        parent::__construct($objectFactory);
    }

    /**
     * Set rate request.
     *
     * @param RateRequest $rateRequest
     * @return void
     */
    public function setRateRequest(RateRequest $rateRequest): void
    {
        $currencyCode = $rateRequest->getBaseCurrency();
        if ($currencyCode instanceof Currency) {
            $currencyCode = $currencyCode->getCurrencyCode();
        }

        $this->_set(OrderItemInterface::CURRENCY, $currencyCode);
    }

    /**
     * Set quote item.
     *
     * @param ItemInterface|\Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @return void
     */
    public function setQuoteItem(ItemInterface $quoteItem): void
    {
        $weightUom = $this->config->getWeightUnit($quoteItem->getQuote()->getStoreId());
        $dimensionsUom = ($weightUom === 'kgs') ? 'cm' : 'in';

        $itemAmount = $quoteItem->getData('base_price');
        $itemAmount -= $quoteItem->getBaseDiscountAmount() / $quoteItem->getQty();

        $mappedProductAttributes = $this->productAttributeMapper->getMappedProductAttributesFromQuoteItem($quoteItem);

        $this->_set(
            OrderItemInterface::PRODUCT_ID,
            $quoteItem->getData('product_id')
        );
        $this->_set(
            OrderItemInterface::QTY,
            $quoteItem->getQty()
        );
        $this->_set(
            OrderItemInterface::SKU,
            array_key_exists('sku', $mappedProductAttributes) ?
                $mappedProductAttributes['sku'] :
                $quoteItem->getData('sku')
        );
        $this->_set(
            OrderItemInterface::NAME,
            array_key_exists('name', $mappedProductAttributes) ?
                $mappedProductAttributes['name'] :
                $quoteItem->getData('name')
        );
        $this->_set(
            OrderItemInterface::DESCRIPTION,
            array_key_exists('description', $mappedProductAttributes) ?
                $mappedProductAttributes['description'] :
                $quoteItem->getData('description')
        );
        $this->_set(
            OrderItemInterface::CATEGORIES,
            $this->quoteItemAttributeReader->getCategoryNames($quoteItem)
        );
        $this->_set(
            OrderItemInterface::DIMENSIONS_UOM,
            array_key_exists('dimensions.unit', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.unit'] :
                $dimensionsUom
        );
        $this->_set(
            OrderItemInterface::LENGTH,
            array_key_exists('dimensions.length', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.length'] :
                $this->quoteItemAttributeReader->getLength($quoteItem)
        );
        $this->_set(
            OrderItemInterface::WIDTH,
            array_key_exists('dimensions.width', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.width'] :
                $this->quoteItemAttributeReader->getWidth($quoteItem)
        );
        $this->_set(
            OrderItemInterface::HEIGHT,
            array_key_exists('dimensions.height', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.height'] :
                $this->quoteItemAttributeReader->getHeight($quoteItem)
        );
        $this->_set(
            OrderItemInterface::WEIGHT_UOM,
            array_key_exists('weight.unit', $mappedProductAttributes) ?
                $mappedProductAttributes['weight.unit'] :
                $weightUom
        );
        $this->_set(
            OrderItemInterface::WEIGHT,
            array_key_exists('weight.value', $mappedProductAttributes) ?
                $mappedProductAttributes['weight.value'] :
                $quoteItem->getData('weight')
        );
        $this->_set(
            OrderItemInterface::AMOUNT,
            array_key_exists('monetaryValue.amount', $mappedProductAttributes) ?
                $mappedProductAttributes['monetaryValue.amount'] :
                $itemAmount
        );
        $this->_set(
            OrderItemInterface::IS_FRAGILE,
            array_key_exists('isFragile', $mappedProductAttributes) ?
                $mappedProductAttributes['isFragile'] :
                null
        );
        $this->_set(
            OrderItemInterface::IS_VIRTUAL,
            array_key_exists('isVirtual', $mappedProductAttributes) ?
                $mappedProductAttributes['isVirtual'] :
                $this->quoteItemAttributeReader->isVirtual($quoteItem)
        );
        $this->_set(
            OrderItemInterface::IS_PREPACKAGED,
            $this->quoteItemAttributeReader->isPrePackaged($quoteItem)
        );
        $this->_set(
            OrderItemInterface::PACKAGE_ID,
            $this->quoteItemAttributeReader->getPackageId($quoteItem)
        );
        $this->_set(
            OrderItemInterface::CAN_ROTATE_VERTICAL,
            array_key_exists('canRotateVertical', $mappedProductAttributes) ?
                $mappedProductAttributes['canRotateVertical'] :
                null
        );
        $this->_set(
            OrderItemInterface::COUNTRY_OF_ORIGIN,
            array_key_exists('origin.address.countryCode', $mappedProductAttributes) ?
                $mappedProductAttributes['origin.address.countryCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::COUNTRY_OF_MANUFACTURE,
            array_key_exists('manufacture.address.countryCode', $mappedProductAttributes) ?
                $mappedProductAttributes['manufacture.address.countryCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::ECCN,
            array_key_exists('classificationCodes.eccn', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.eccn'] :
                ''
        );
        $this->_set(
            OrderItemInterface::SCHEDULE_B_INFO,
            array_key_exists('classificationCodes.scheduleBInfo', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.scheduleBInfo'] :
                ''
        );
        $this->_set(
            OrderItemInterface::HS_CODE,
            array_key_exists('classificationCodes.hsCode', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.hsCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::COMPOSITION,
            array_key_exists('composition', $mappedProductAttributes) ?
                $mappedProductAttributes['composition'] :
                ''
        );
        $this->_set(
            OrderItemInterface::CUSTOM_ATTRIBUTES,
            array_key_exists('customAttributes', $mappedProductAttributes) ?
                $mappedProductAttributes['customAttributes'] :
                []
        );
    }

    /**
     * Set order.
     *
     * @param OrderInterface|\Magento\Sales\Model\Order $order
     * @return void
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->_set(OrderItemInterface::CURRENCY, $order->getBaseCurrencyCode());
    }

    /**
     * Set order item.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return void
     */
    public function setOrderItem(\Magento\Sales\Api\Data\OrderItemInterface $orderItem): void
    {
        $weightUom = $this->config->getWeightUnit($orderItem->getStoreId());
        $dimensionsUom = ($weightUom === 'kgs') ? 'cm' : 'in';

        $itemAmount = $orderItem->getBasePrice();
        $itemAmount -= $orderItem->getBaseDiscountAmount() / $orderItem->getQtyOrdered();

        $mappedProductAttributes = $this->productAttributeMapper->getMappedProductAttributesFromOrderItem($orderItem);

        $this->_set(
            OrderItemInterface::PRODUCT_ID,
            $orderItem->getProductId()
        );
        $this->_set(
            OrderItemInterface::QTY,
            $orderItem->getQtyOrdered()
        );
        $this->_set(
            OrderItemInterface::SKU,
            array_key_exists('sku', $mappedProductAttributes) ?
                $mappedProductAttributes['sku'] :
                $orderItem->getSku()
        );
        $this->_set(
            OrderItemInterface::NAME,
            array_key_exists('name', $mappedProductAttributes) ?
                $mappedProductAttributes['name'] :
                $orderItem->getName()
        );
        $this->_set(
            OrderItemInterface::DESCRIPTION,
            array_key_exists('description', $mappedProductAttributes) ?
                $mappedProductAttributes['description'] :
                $orderItem->getDescription()
        );
        $this->_set(
            OrderItemInterface::CATEGORIES,
            $this->orderItemAttributeReader->getCategoryNames($orderItem)
        );
        $this->_set(
            OrderItemInterface::DIMENSIONS_UOM,
            array_key_exists('dimensions.unit', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.unit'] :
                $dimensionsUom
        );
        $this->_set(
            OrderItemInterface::LENGTH,
            array_key_exists('dimensions.length', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.length'] :
                $this->orderItemAttributeReader->getLength($orderItem)
        );
        $this->_set(
            OrderItemInterface::WIDTH,
            array_key_exists('dimensions.width', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.width'] :
                $this->orderItemAttributeReader->getWidth($orderItem)
        );
        $this->_set(
            OrderItemInterface::HEIGHT,
            array_key_exists('dimensions.height', $mappedProductAttributes) ?
                $mappedProductAttributes['dimensions.height'] :
                $this->orderItemAttributeReader->getHeight($orderItem)
        );
        $this->_set(
            OrderItemInterface::WEIGHT_UOM,
            array_key_exists('weight.unit', $mappedProductAttributes) ?
                $mappedProductAttributes['weight.unit'] :
                $weightUom
        );
        $this->_set(
            OrderItemInterface::WEIGHT,
            array_key_exists('weight.value', $mappedProductAttributes) ?
                $mappedProductAttributes['weight.value'] :
                $orderItem->getWeight()
        );
        $this->_set(
            OrderItemInterface::AMOUNT,
            array_key_exists('monetaryValue.amount', $mappedProductAttributes) ?
                $mappedProductAttributes['monetaryValue.amount'] :
                $itemAmount
        );
        $this->_set(
            OrderItemInterface::IS_FRAGILE,
            array_key_exists('isFragile', $mappedProductAttributes) ?
                $mappedProductAttributes['isFragile'] :
                null
        );
        $this->_set(
            OrderItemInterface::IS_VIRTUAL,
            array_key_exists('isVirtual', $mappedProductAttributes) ?
                $mappedProductAttributes['isVirtual'] :
                $orderItem->getIsVirtual()
        );
        $this->_set(
            OrderItemInterface::IS_PREPACKAGED,
            $this->orderItemAttributeReader->isPrePackaged($orderItem)
        );
        $this->_set(
            OrderItemInterface::PACKAGE_ID,
            $this->orderItemAttributeReader->getPackageId($orderItem)
        );
        $this->_set(
            OrderItemInterface::CAN_ROTATE_VERTICAL,
            array_key_exists('canRotateVertical', $mappedProductAttributes) ?
                $mappedProductAttributes['canRotateVertical'] :
                null
        );
        $this->_set(
            OrderItemInterface::COUNTRY_OF_ORIGIN,
            array_key_exists('origin.address.countryCode', $mappedProductAttributes) ?
                $mappedProductAttributes['origin.address.countryCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::COUNTRY_OF_MANUFACTURE,
            array_key_exists('manufacture.address.countryCode', $mappedProductAttributes) ?
                $mappedProductAttributes['manufacture.address.countryCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::ECCN,
            array_key_exists('classificationCodes.eccn', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.eccn'] :
                ''
        );
        $this->_set(
            OrderItemInterface::SCHEDULE_B_INFO,
            array_key_exists('classificationCodes.scheduleBInfo', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.scheduleBInfo'] :
                ''
        );
        $this->_set(
            OrderItemInterface::HS_CODE,
            array_key_exists('classificationCodes.hsCode', $mappedProductAttributes) ?
                $mappedProductAttributes['classificationCodes.hsCode'] :
                ''
        );
        $this->_set(
            OrderItemInterface::COMPOSITION,
            array_key_exists('composition', $mappedProductAttributes) ?
                $mappedProductAttributes['composition'] :
                ''
        );
        $this->_set(
            OrderItemInterface::CUSTOM_ATTRIBUTES,
            array_key_exists('customAttributes', $mappedProductAttributes) ?
                $mappedProductAttributes['customAttributes'] :
                []
        );
    }
}
