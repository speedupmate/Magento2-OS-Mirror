<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\FlexField\Extractor\AttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavAttributeExtractor;
use Vertex\Tax\Model\FlexField\Extractor\EavValueExtractor;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;

/**
 * @inheritDoc
 */
class ProductGetterProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    /** @var string[] */
    const BLOCK_LIST = [
        'getExtensionAttributes',
        'getMediaGalleryEntries',
        'getProductLinks',
        'getOptions',
        'getTierPrices',
        'getCustomAttribute',
        'getCustomAttributes',

        'getId',
        'getAttributeSetId',
        'getStatus',
        'getVisibility',
        'getSku',
        'getCreatedAt',
        'getUpdatedAt',
        'getPrice'
    ];

    /** @var string[] */
    const DATE_FIELDS = [];

    /** @var string[] */
    const EAV_ATTRIBUTE_BLOCK_LIST = [
        'name',
        'description',
        'short_description',
        'price',
        'special_price',
        'special_from_date',
        'special_to_date',
        'weight',
        'manufacturer',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'image',
        'small_image',
        'thumbnail',
        'old_id',
        'tier_price',
        'color',
        'news_from_date',
        'news_to_date',
        'gallery',
        'status',
        'minimal_price',
        'visibility',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'options_container',
        'image_label',
        'small_image_label',
        'thumbnail_label',
        'country_of_manufacture',
        'quantity_and_stock_status',
        'custom_layout',
        'msrp_display_actual_price_type',
        'url_key',
        'url_path',
        'links_purchased_separately',
        'samples_title',
        'links_title',
        'links_exist',
        'gift_message_available',
        'swatch_image',
        'tax_class_id',
        'price_type',
        'sku_type',
        'weight_type',
        'price_view',
        'shipment_type',
        'ts_dimensions_length',
        'ts_dimensions_width',
        'ts_dimensions_height',
        'email_template',
        'gift_wrapping_available',
        'is_returnable',
        'giftcard_amounts',
        'allow_open_amount',
        'open_amount_min',
        'open_amount_max',
        'giftcard_type',
        'is_redeemable',
        'use_config_is_redeemable',
        'lifetime',
        'use_config_lifetime',
        'use_config_email_template',
        'allow_message',
        'use_config_allow_message',
        'related_tgtr_position_limit',
        'related_tgtr_position_behavior',
        'upsell_tgtr_position_limit',
        'upsell_tgtr_position_behavior',
        'gift_wrapping_price'
    ];

    /** @var string */
    const PREFIX = 'product';

    /** @var AttributeExtractor */
    private $attributeExtractor;

    /** @var AttributeRenamer */
    private $attributeRenamer;

    /** @var EavAttributeExtractor */
    private $eavAttributeExtractor;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var EavValueExtractor */
    private $valueExtractor;

    /**
     * @param AttributeExtractor $attributeExtractor
     * @param EavAttributeExtractor $eavAttributeExtractor
     * @param EavValueExtractor $valueExtractor
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRenamer $attributeRenamer
     */
    public function __construct(
        AttributeExtractor $attributeExtractor,
        EavAttributeExtractor $eavAttributeExtractor,
        EavValueExtractor $valueExtractor,
        ProductRepositoryInterface $productRepository,
        AttributeRenamer $attributeRenamer
    ) {
        $this->attributeExtractor = $attributeExtractor;
        $this->eavAttributeExtractor = $eavAttributeExtractor;
        $this->valueExtractor = $valueExtractor;
        $this->productRepository = $productRepository;
        $this->attributeRenamer = $attributeRenamer;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $optionGroup = 'Product';

        /** @var FlexFieldProcessableAttribute[] $results */
        return $this->attributeRenamer->execute(
            array_merge(
                $this->attributeExtractor->extract(
                    ProductInterface::class,
                    static::PREFIX,
                    $optionGroup,
                    static::class,
                    array_merge(static::DATE_FIELDS, static::BLOCK_LIST)
                ),
                $this->attributeExtractor->extractDateFields(
                    static::PREFIX,
                    static::DATE_FIELDS,
                    $optionGroup,
                    static::class
                ),
                $this->eavAttributeExtractor->extract(
                    Product::ENTITY,
                    static::PREFIX,
                    $optionGroup,
                    static::class,
                    static::EAV_ATTRIBUTE_BLOCK_LIST
                )
            ),
            [static::PREFIX . '.getTypeId' => __('Product Type')]
        );
    }

    /**
     * @inheritdoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        return $this->getValueFromProductId($item->getProductId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getValueFromProductId($item->getProductId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getValueFromProductId($item->getProductId(), $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes || !$extAttributes->getProductId()) {
            return null;
        }

        return $this->getValueFromProductId($extAttributes->getProductId(), $attributeCode);
    }

    /**
     * Retrieve a list of custom date attribute codes
     *
     * @return array
     */
    private function getCustomDateAttributes()
    {
        return $this->eavAttributeExtractor->getCustomDateAttributeCodes(Product::ENTITY);
    }

    /**
     * Extract the value of a getter given a Product ID and attribute code
     *
     * @param int $productId
     * @param string $attributeCode
     * @return int|string|null
     */
    private function getValueFromProductId($productId, $attributeCode)
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $this->valueExtractor->extract(
                $product,
                Product::ENTITY,
                $attributeCode,
                static::PREFIX,
                array_merge(static::DATE_FIELDS, $this->getCustomDateAttributes())
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
