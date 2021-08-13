<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Vertex\Tax\Model\Config\DeliveryTerm;

/**
 * Configuration retrieval tool
 */
class Config
{
    const CONFIG_XML_PATH_DEFAULT_CUSTOMER_CODE = 'tax/classes/default_customer_code';
    const CONFIG_XML_PATH_DEFAULT_TAX_CALCULATION_ADDRESS_TYPE = 'tax/calculation/based_on';
    const CONFIG_XML_PATH_ENABLE_TAX_CALCULATION = 'tax/vertex_settings/use_for_calculation';
    const CONFIG_XML_PATH_ENABLE_VERTEX = 'tax/vertex_settings/enable_vertex';
    const CONFIG_XML_PATH_FLEXFIELDS_CODE = 'tax/vertex_flexfields/code';
    const CONFIG_XML_PATH_FLEXFIELDS_DATE = 'tax/vertex_flexfields/date';
    const CONFIG_XML_PATH_FLEXFIELDS_NUMERIC = 'tax/vertex_flexfields/numeric';
    const CONFIG_XML_PATH_PRINTED_CARD_PRICE = 'sales/gift_options/printed_card_price';
    const CONFIG_XML_PATH_SHIPPING_TAX_CLASS = 'tax/classes/shipping_tax_class';
    const CONFIG_XML_PATH_TAX_APPLY_ON = 'tax/calculation/apply_tax_on';
    const CONFIG_XML_PATH_TAX_DISPLAY_IN_CATALOG = 'tax/display/type';
    const CONFIG_XML_PATH_VERTEX_API_KEY = 'tax/vertex_settings/password';
    const CONFIG_XML_PATH_VERTEX_API_TRUSTED_ID = 'tax/vertex_settings/trustedId';
    const CONFIG_XML_PATH_VERTEX_API_USER = 'tax/vertex_settings/login';
    const CONFIG_XML_PATH_VERTEX_CITY = 'tax/vertex_seller_info/city';
    const CONFIG_XML_PATH_VERTEX_COMPANY_CODE = 'tax/vertex_seller_info/company';
    const CONFIG_XML_PATH_VERTEX_COUNTRY = 'tax/vertex_seller_info/country_id';
    const CONFIG_XML_PATH_VERTEX_DELIVERY_TERM_DEFAULT = 'tax/vertex_delivery_terms/default_term';
    const CONFIG_XML_PATH_VERTEX_DELIVERY_TERM_OVERRIDE = 'tax/vertex_delivery_terms/override';
    const CONFIG_XML_PATH_VERTEX_ENABLE_WSDL_CACHE = 'tax/vertex_wsdl_cache/enable_wsdl_cache';
    const CONFIG_XML_PATH_VERTEX_INVOICE_DATE = 'tax/vertex_settings/invoice_tax_date';
    const CONFIG_XML_PATH_VERTEX_INVOICE_ORDER = 'tax/vertex_settings/invoice_order';
    const CONFIG_XML_PATH_VERTEX_INVOICE_ORDER_STATUS = 'tax/vertex_settings/invoice_order_status';
    const CONFIG_XML_PATH_VERTEX_LOCATION_CODE = 'tax/vertex_seller_info/location_code';
    const CONFIG_XML_PATH_VERTEX_WSDL_CACHE_RUNTIME = 'tax/vertex_wsdl_cache/rotation_runtime';
    const CONFIG_XML_PATH_VERTEX_POSTAL_CODE = 'tax/vertex_seller_info/postalCode';
    const CONFIG_XML_PATH_VERTEX_REGION = 'tax/vertex_seller_info/region_id';
    const CONFIG_XML_PATH_VERTEX_SHIPPING_ORIGIN_SOURCE = 'tax/vertex_seller_info/shipping_origin_source';
    const CONFIG_XML_PATH_VERTEX_STREET1 = 'tax/vertex_seller_info/streetAddress1';
    const CONFIG_XML_PATH_VERTEX_STREET2 = 'tax/vertex_seller_info/streetAddress2';
    const CRON_WSDL_STRING_PATH = 'crontab/vertex/jobs/vertex_wsdl_cache/schedule/cron_expr';
    const MAX_CHAR_PRODUCT_CODE_ALLOWED = 40;
    const VALUE_APPLY_ON_CUSTOM = 0;
    const VALUE_APPLY_ON_ORIGINAL_ONLY = 1;
    const VERTEX_ADDRESS_API_HOST = 'tax/vertex_settings/address_api_url';
    const VERTEX_ALLOWED_COUNTRIES = 'tax/vertex_settings/allowed_countries';
    const VERTEX_API_HOST = 'tax/vertex_settings/api_url';
    const VERTEX_COUNTRY_SORT_REGION = 'tax/vertex_settings/country_sort_by_region';
    const VERTEX_CREDITMEMO_ADJUSTMENT_CLASS = 'tax/classes/creditmemo_adjustment_class';
    const VERTEX_CREDITMEMO_ADJUSTMENT_NEGATIVE_CODE = 'tax/classes/creditmemo_adjustment_negative_code';
    const VERTEX_CREDITMEMO_ADJUSTMENT_POSITIVE_CODE = 'tax/classes/creditmemo_adjustment_positive_code';
    const VERTEX_FPT_CUSTOM_TAX_CLASS_ENABLE = 'tax/classes/custom_tax_class';
    const VERTEX_FPT_ITEM_CODE = 'tax/classes/fpt_item_code';
    const VERTEX_FPT_TAX_CLASS = 'tax/classes/fpt_tax_class';
    const VERTEX_GIFTWRAP_ITEM_CLASS = 'tax/classes/giftwrap_item_class';
    const VERTEX_GIFTWRAP_ITEM_CODE_PREFIX = 'tax/classes/giftwrap_item_code';
    const VERTEX_GIFTWRAP_ORDER_CLASS = 'tax/classes/giftwrap_order_class';
    const VERTEX_GIFTWRAP_ORDER_CODE = 'tax/classes/giftwrap_order_code';
    const VERTEX_PRINTED_GIFTCARD_CLASS = 'tax/classes/printed_giftcard_class';
    const VERTEX_PRINTED_GIFTCARD_CODE = 'tax/classes/printed_giftcard_code';
    const VERTEX_SUMMARIZE_TAX = 'tax/vertex_settings/summarize_tax';
    const XML_PATH_FPT_ENABLED = 'tax/weee/enable';
    const XML_PATH_FPT_TAXABLE = 'tax/weee/apply_vat';

    /** @var DeliveryTerm */
    private $deliveryTermConfig;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DeliveryTerm $deliveryTermConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig, DeliveryTerm $deliveryTermConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->deliveryTermConfig = $deliveryTermConfig;
    }

    /**
     * Retrieve list of countries Vertex should be used for
     *
     * @param string|null $store
     * @param string $scope
     * @return string[] of two character ISO country codes
     */
    public function getAllowedCountries($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return explode(',', $this->getConfigValue(self::VERTEX_ALLOWED_COUNTRIES, $store, $scope));
    }

    /**
     * Retrieve which price we should be applying tax to
     *
     * @param null $store
     * @param string $scope
     * @return string
     */
    public function getApplyTaxOn($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_TAX_APPLY_ON, $store, $scope);
    }

    /**
     * Get the City of the Company Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyCity($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $city = $this->getConfigValue(Shipment::XML_PATH_STORE_CITY, $store, $scope);
        } else {
            $city = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_CITY, $store, $scope);
        }

        return $city ?? null;
    }

    /**
     * Retrieve the Company Code
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_COMPANY_CODE, $store, $scope);
    }

    /**
     * Get the Country of the Company Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyCountry($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $country = $this->getConfigValue(Shipment::XML_PATH_STORE_COUNTRY_ID, $store, $scope);
        } else {
            $country = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_COUNTRY, $store, $scope);
        }

        return $country ?? null;
    }

    /**
     * Get the Postal Code of the Company Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyPostalCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $postalCode = $this->getConfigValue(Shipment::XML_PATH_STORE_ZIP, $store, $scope);
        } else {
            $postalCode = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_POSTAL_CODE, $store, $scope);
        }

        return $postalCode ?? null;
    }

    /**
     * Get the Region ID of the Company Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyRegionId($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $region = $this->getConfigValue(Shipment::XML_PATH_STORE_REGION_ID, $store, $scope);
        } else {
            $region = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_REGION, $store, $scope);
        }

        return $region ?? null;
    }

    /**
     * Get Line 1 of the Company Street Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyStreet1($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $street = $this->getConfigValue(Shipment::XML_PATH_STORE_ADDRESS1, $store, $scope);
        } else {
            $street = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_STREET1, $store, $scope);
        }

        return $street ?? null;
    }

    /**
     * Get Line 2 of the Company Street Address
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCompanyStreet2($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        if ($this->isShippingOriginSourceEnabled($store, $scope)) {
            $street = $this->getConfigValue(Shipment::XML_PATH_STORE_ADDRESS2, $store, $scope);
        } else {
            $street = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_STREET2, $store, $scope);
        }

        return $street ?? null;
    }

    /**
     * Retrieve a value from the configuration within a scope
     *
     * @param string $value
     * @param string|null $scopeId
     * @param string|null $scope
     * @return mixed
     */
    public function getConfigValue($value, $scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($value, $scope, $scopeId);
    }

    /**
     * Get the Tax class for a creditmemo adjustment fee
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCreditmemoAdjustmentFeeClass($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_CLASS, $store, $scope);
    }

    /**
     * Get the code for a creditmemo adjustment fee
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCreditmemoAdjustmentFeeCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_NEGATIVE_CODE, $store, $scope);
    }

    /**
     * Get the tax class for a positive adjustment on a creditmemo
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCreditmemoAdjustmentPositiveClass($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_CLASS, $store, $scope);
    }

    /**
     * Get the positive adjustment code for a creditmemo
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getCreditmemoAdjustmentPositiveCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_CREDITMEMO_ADJUSTMENT_POSITIVE_CODE, $store, $scope);
    }

    /**
     * Retrieve the time of day logs should be rotated
     *
     * @return string
     */
    public function getCronWsdlCacheTime()
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_WSDL_CACHE_RUNTIME);
    }

    /**
     * Get the Default Customer Code
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getDefaultCustomerCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_CUSTOMER_CODE, $store, $scope);
    }

    /**
     * Get the default Delivery Term
     *
     * @param string|null $store
     * @param string $scope
     * @return string
     */
    public function getDefaultDeliveryTerm($store = null, $scope = ScopeInterface::SCOPE_WEBSITE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_DELIVERY_TERM_DEFAULT, $store, $scope);
    }

    /**
     * Get the Delivery Term to Override
     *
     * @param string|null $store
     * @param string $scope
     * @return array
     */
    public function getDeliveryTermOverride($store = null, $scope = ScopeInterface::SCOPE_WEBSITE)
    {
        $configValue = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_DELIVERY_TERM_OVERRIDE, $store, $scope);

        return $this->deliveryTermConfig->unserializeValue($configValue);
    }

    /**
     * Retrieve all selected flexible fields
     *
     * @param string|null $store
     * @param string $scope
     * @return array
     */
    public function getFlexFieldsList($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return array_filter(
            array_merge(
                $this->getFlexFields(static::CONFIG_XML_PATH_FLEXFIELDS_CODE, $store, $scope),
                $this->getFlexFields(static::CONFIG_XML_PATH_FLEXFIELDS_NUMERIC, $store, $scope),
                $this->getFlexFields(static::CONFIG_XML_PATH_FLEXFIELDS_DATE, $store, $scope)
            ),
            static function ($entry) {
                return !empty($entry) && $entry['field_source'] !== 'none';
            }
        );
    }

    /**
     * Get the Tax Class for Item-level Giftwrapping
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getGiftWrappingItemClass($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ITEM_CLASS, $store, $scope);
    }

    /**
     * Get the code prefix for Item-level Giftwrapping
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getGiftWrappingItemCodePrefix($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ITEM_CODE_PREFIX, $store, $scope);
    }

    /**
     * Get the Tax Class for Order-level Giftwrapping
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getGiftWrappingOrderClass($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ORDER_CLASS, $store, $scope);
    }

    /**
     * Get the code for Order-level Giftwrapping
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getGiftWrappingOrderCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_GIFTWRAP_ORDER_CODE, $store, $scope);
    }

    /**
     * The prefix value for the the fixed product tax for invoice
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function getItemPrefixCodeForFixedProductTax($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_FPT_ITEM_CODE, $store, $scope);
    }

    /**
     * Retrieve a list of countries grouped by Vertex region
     *
     * @param string|null $store
     * @param string $scope
     * @return array A multi-dimensional array where the top level key is the Vertex region the country is associated
     *     with and the value is an array of country codes
     */
    public function getListForAllowedCountrySort($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $returnArray = json_decode($this->getConfigValue(self::VERTEX_COUNTRY_SORT_REGION, $store, $scope), true);

        return $returnArray ?: [];
    }

    /**
     * Retrieve the Location Code
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getLocationCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_LOCATION_CODE, $store, $scope);
    }

    /**
     * Retrieve the price of a Printed Gift Card
     *
     * @param string|null $store
     * @param string $scope
     * @return mixed
     */
    public function getPrintedCardPrice($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_PRINTED_CARD_PRICE, $store, $scope);
    }

    /**
     * Get the Tax Class for a Printed Gift Card
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getPrintedGiftcardClass($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_PRINTED_GIFTCARD_CLASS, $store, $scope);
    }

    /**
     * Get the code for a Printed Gift Card
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getPrintedGiftcardCode($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_PRINTED_GIFTCARD_CODE, $store, $scope);
    }

    /**
     * Get the Tax Class ID to be used for Shipping
     *
     * @param string|null $store
     * @param string $scope
     * @return float|null
     */
    public function getShippingTaxClassId($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store, $scope);
    }

    /**
     * Determine how customer receive their tax summaries
     *
     * @param string|null $store
     * @param string $scope
     * @return string
     */
    public function getSummarizeTax($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_SUMMARIZE_TAX, $store, $scope);
    }

    /**
     * Get the Trusted ID for the Vertex Integration
     *
     * @param string|null $store
     * @param string $scope
     * @return string|null
     */
    public function getTrustedId($store = null, $scope = ScopeInterface::SCOPE_STORE): ?string
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_API_TRUSTED_ID, $store, $scope);
    }

    /**
     * Get the URL of the Tax Area Lookup API Endpoint
     *
     * @param string|null $store
     * @param string $scope
     * @return string|null
     */
    public function getVertexAddressHost($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_ADDRESS_API_HOST, $store, $scope);
    }

    /**
     * Get the URL of the Quotation and Invoicing API Endpoint
     *
     * @param string|null $store
     * @param string $scope
     * @return string|null
     */
    public function getVertexHost($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_API_HOST, $store, $scope);
    }

    /**
     * Grab the Order Status during which we should commit to the Tax Log
     *
     * @param string|null $store
     * @param string $scope
     * @return string
     */
    public function invoiceOrderStatus($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER_STATUS, $store, $scope);
    }

    /**
     * Determine whether or not tax is turned on to display in the catalog
     *
     * @param string|null $store
     * @param string $scope
     * @return bool
     */
    public function isDisplayPriceInCatalogEnabled($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $configValue = $this->getConfigValue(self::CONFIG_XML_PATH_TAX_DISPLAY_IN_CATALOG, $store, $scope);

        return (int)$configValue !== TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Determine if the fixed product tax is enabled
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function isFixedProductTaxEnabled($scopeId = null, $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FPT_ENABLED, $scope, $scopeId);
    }

    /**
     * Determine if the FPT is taxable
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function isFixedProductTaxTaxable($scopeId = null, $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FPT_TAXABLE, $scope, $scopeId);
    }

    /**
     * Determine if Vertex Archiving has been enabled.
     *
     * @return bool
     */
    public function isWsdlCacheEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_VERTEX_ENABLE_WSDL_CACHE);
    }

    /**
     * Determine if tax calculation is enabled
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function isTaxCalculationEnabled($scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_ENABLE_TAX_CALCULATION, $scope, $scopeId);
    }

    /**
     * Determine if Vertex has been enabled
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function isVertexActive($scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_ENABLE_VERTEX, $scope, $scopeId);
    }

    /**
     * Determine if the FPT custom tax is enabled
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function isVertexFixedProductTaxCustom($scopeId = null, $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(self::VERTEX_FPT_CUSTOM_TAX_CLASS_ENABLE, $scope, $scopeId);
    }

    /**
     * Determine if we commit to the Tax Log during Invoice Creation or not
     *
     * @param string|null $store
     * @param string $scope
     * @return bool
     */
    public function requestByInvoiceCreation($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $vertexInvoiceEvent = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER, $store, $scope);

        return $vertexInvoiceEvent === 'invoice_created';
    }

    /**
     * Determine if we commit to the Tax Log during an Order Status change or not
     *
     * @param string|null $store
     * @param string $scope
     * @return bool
     */
    public function requestByOrderStatus($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $vertexInvoiceEvent = $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_INVOICE_ORDER, $store, $scope);

        return $vertexInvoiceEvent === 'order_status';
    }

    /**
     * The tax class selected that is to be used for FPT
     *
     * @param string|null $scopeId
     * @param string $scope
     * @return bool
     */
    public function vertexTaxClassUsedForFixedProductTax($store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(self::VERTEX_FPT_TAX_CLASS, $store, $scope);
    }

    /**
     * Retrieve all flexible fields for quotes
     *
     * @param string $value
     * @param string|null $store
     * @param string $scope
     * @return array
     */
    private function getFlexFields($value, $store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $allAttributes = json_decode(
            $this->getConfigValue($value, $store, $scope),
            true
        );

        if (empty($allAttributes)) {
            return [];
        }
        $attributes = array_map(
            static function ($data) {
                return empty($data['field_source']) ? false : $data;
            },
            $allAttributes
        );

        return array_filter($attributes);
    }

    private function isShippingOriginSourceEnabled($scopeId = null, $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_VERTEX_SHIPPING_ORIGIN_SOURCE, $scope, $scopeId);
    }
}
