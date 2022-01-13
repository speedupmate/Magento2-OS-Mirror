<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Mapper\Api60;

use Vertex\Data\DeliveryTerm;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Data\FlexibleFieldInterface;
use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Mapper\CustomerMapperInterface;
use Vertex\Mapper\FlexibleCodeFieldMapperInterface;
use Vertex\Mapper\FlexibleDateFieldMapperInterface;
use Vertex\Mapper\FlexibleNumericFieldMapperInterface;
use Vertex\Mapper\LineItemMapperInterface;
use Vertex\Mapper\MapperUtilities;
use Vertex\Mapper\SellerMapperInterface;
use Vertex\Mapper\TaxMapperInterface;

/**
 * API Level 60 implementation of {@see LineItemMapperInterface}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LineItemMapper implements LineItemMapperInterface
{
    /** Maximum characters allowed for commodity code */
    const COMMODITY_CODE_MAX = 40;
    /** Minimum characters allowed for commodity code */
    const COMMODITY_CODE_MIN = 0;
    /** Maximum characters allowed for commodity type code */
    const COMMODITY_CODE_TYPE_MAX = 60;
    /** Minimum characters allowed for commodity type code */
    const COMMODITY_CODE_TYPE_MIN = 0;
    /** @var int Minimum characters allowed for product code  */
    const PRODUCT_CODE_MAX = 40;
    /** @var int Minimum characters allowed for product code */
    const PRODUCT_CODE_MIN = 0;
    /** @var int Maximum characters allowed for product tax class name */
    const PRODUCT_TAX_CLASS_NAME_MAX = 40;
    /** @var int Minimum characters allowed for product tax class name */
    const PRODUCT_TAX_CLASS_NAME_MIN = 0;

    /** @var CustomerMapperInterface */
    private $customerMapper;

    /** @var FlexibleCodeFieldMapperInterface */
    private $flexibleCodeFieldMapper;

    /** @var FlexibleDateFieldMapperInterface */
    private $flexibleDateFieldMapper;

    /** @var FlexibleNumericFieldMapperInterface */
    private $flexibleNumericFieldMapper;

    /** @var SellerMapperInterface */
    private $sellerMapper;

    /** @var TaxMapperInterface */
    private $taxMapper;

    /** @var MapperUtilities */
    private $utilities;

    /**
     * @param MapperUtilities|null $utilities
     * @param CustomerMapperInterface|null $customerMapper
     * @param SellerMapperInterface|null $sellerMapper
     * @param TaxMapperInterface|null $taxMapper
     * @param FlexibleCodeFieldMapperInterface|null $flexibleCodeFieldMapper
     * @param FlexibleNumericFieldMapperInterface|null $flexibleNumericFieldMapper
     * @param FlexibleDateFieldMapperInterface|null $flexibleDateFieldMapper
     */
    public function __construct(
        MapperUtilities $utilities = null,
        CustomerMapperInterface $customerMapper = null,
        SellerMapperInterface $sellerMapper = null,
        TaxMapperInterface $taxMapper = null,
        FlexibleCodeFieldMapperInterface $flexibleCodeFieldMapper = null,
        FlexibleNumericFieldMapperInterface $flexibleNumericFieldMapper = null,
        FlexibleDateFieldMapperInterface $flexibleDateFieldMapper = null
    ) {
        $this->utilities = $utilities ?: new MapperUtilities();
        $this->customerMapper = $customerMapper ?: new CustomerMapper();
        $this->sellerMapper = $sellerMapper ?: new SellerMapper();
        $this->taxMapper = $taxMapper ?: new TaxMapper();
        $this->flexibleCodeFieldMapper = $flexibleCodeFieldMapper ?: new FlexibleCodeFieldMapper();
        $this->flexibleNumericFieldMapper = $flexibleNumericFieldMapper ?: new FlexibleNumericFieldMapper();
        $this->flexibleDateFieldMapper = $flexibleDateFieldMapper ?: new FlexibleDateFieldMapper();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function build(\stdClass $map)
    {
        $object = new LineItem();
        if (isset($map->Customer)) {
            $object->setCustomer($this->customerMapper->build($map->Customer));
        }
        if (isset($map->Seller)) {
            $object->setSeller($this->sellerMapper->build($map->Seller));
        }
        if (isset($map->deliveryTerm)) {
            $object->setDeliveryTerm($map->deliveryTerm);
        }
        if (isset($map->lineItemId)) {
            $object->setLineItemId($map->lineItemId);
        }
        if (isset($map->locationCode)) {
            $object->setLocationCode($map->locationCode);
        }
        $this->buildProduct($map, $object);

        if (isset($map->ExtendedPrice)) {
            $object->setExtendedPrice(
                $map->ExtendedPrice instanceof \stdClass ? $map->ExtendedPrice->_ : $map->ExtendedPrice
            );
        }
        if (isset($map->Quantity)) {
            $object->setQuantity(
                $map->Quantity instanceof \stdClass ? $map->Quantity->_ : $map->Quantity
            );
        }
        if (isset($map->TotalTax)) {
            $object->setTotalTax(
                $map->TotalTax instanceof \stdClass ? $map->TotalTax->_ : $map->TotalTax
            );
        }
        if (isset($map->UnitPrice)) {
            $object->setUnitPrice(
                $map->UnitPrice instanceof \stdClass ? $map->UnitPrice->_ : $map->UnitPrice
            );
        }
        if (isset($map->Taxes)) {
            $rawTaxes = $map->Taxes instanceof \stdClass ? [$map->Taxes] : $map->Taxes;
        } else {
            $rawTaxes = [];
        }
        if (isset($map->taxIncludedIndicator)) {
            $object->setTaxIncluded($map->taxIncludedIndicator);
        }
        $taxes = [];
        foreach ($rawTaxes as $rawTax) {
            $taxes[] = $this->taxMapper->build($rawTax);
        }
        $object->setTaxes($taxes);
        $this->buildCommodityCode($map, $object);
        $this->buildFlexibleFields($map, $object);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getProductCodeMaxLength()
    {
        return static::PRODUCT_CODE_MAX;
    }

    /**
     * @inheritDoc
     */
    public function getProductCodeMinLength()
    {
        return static::PRODUCT_CODE_MIN;
    }

    /**
     * @inheritDoc
     */
    public function validateProductCode($fieldValue)
    {
        // TODO: Implement validateProductCode() method.
    }

    /**
     * @inheritDoc
     */
    public function getProductTaxClassNameMaxLength()
    {
        return static::PRODUCT_TAX_CLASS_NAME_MAX;
    }

    /**
     * @inheritDoc
     */
    public function getProductTaxClassNameMinLength()
    {
        return static::PRODUCT_TAX_CLASS_NAME_MIN;
    }

    /**
     * @inheritDoc
     */
    public function validateProductTaxClassName($fieldValue)
    {
        // TODO: Implement validateProductTaxClassName() method.
    }

    /**
     * @inheritdoc
     */
    public function map(LineItemInterface $object)
    {
        $map = new \stdClass();

        $map = $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getLocationCode(),
            'locationCode',
            0,
            20,
            true,
            'Location Code'
        );

        $map = $this->addDeliveryTermToMap($object, $map);

        $map = $this->utilities->addToMapWithLengthValidation(
            $map,
            $object->getLineItemId(),
            'lineItemId',
            1,
            40,
            true,
            'Line Item ID'
        );

        $this->addSellerToMap($map, $object);

        $this->addCustomerToMap($object, $map);

        $this->addProductToMap($object, $map);

        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getQuantity(),
            'Quantity'
        );

        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getUnitPrice(),
            'UnitPrice',
            PHP_INT_MIN,
            PHP_INT_MAX,
            true,
            'Unit Price'
        );

        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getExtendedPrice(),
            'ExtendedPrice',
            PHP_INT_MIN,
            PHP_INT_MAX,
            true,
            'Extended Price'
        );

        $taxes = $object->getTaxes();
        $mapTaxes = [];
        foreach ($taxes as $tax) {
            $mapTaxes[] = $this->taxMapper->map($tax);
        }

        if (!empty($mapTaxes)) {
            $map->Taxes = $mapTaxes;
        }

        $map = $this->utilities->addToMapWithDecimalValidation(
            $map,
            $object->getTotalTax(),
            'TotalTax',
            PHP_INT_MIN,
            PHP_INT_MAX,
            true,
            'Total Tax'
        );
        if ($object->isTaxIncluded() !== null) {
            $map->taxIncludedIndicator = $object->isTaxIncluded();
        }

        $this->addCommodityToMap($map, $object);

        if (count($object->getFlexibleFields())) {
            $map->FlexibleFields = new \stdClass();
        }
        $this->mapFlexibleFields($object, $map);

        return $map;
    }

    /**
     * Add Commodity to SOAP map object
     *
     * @param \stdClass $map
     * @param LineItemInterface $object
     * @throws \Vertex\Exception\ValidationException
     */
    private function addCommodityToMap(\stdClass $map, LineItemInterface $object)
    {
        if ($object->getCommodityCode() !== null) {
            $map->CommodityCode = new \stdClass();
            $map->CommodityCode = $this->utilities->addToMapWithLengthValidation(
                $map->CommodityCode,
                $object->getCommodityCode(),
                '_',
                static::COMMODITY_CODE_MIN,
                static::COMMODITY_CODE_MAX,
                true,
                'Commodity Code'
            );
            if ($object->getCommodityCodeType() !== null) {
                $map->CommodityCode = $this->utilities->addToMapWithLengthValidation(
                    $map->CommodityCode,
                    $object->getCommodityCodeType(),
                    'commodityCodeType',
                    static::COMMODITY_CODE_TYPE_MIN,
                    static::COMMODITY_CODE_TYPE_MAX,
                    false,
                    'Commodity Code Type'
                );
            }
        }
    }

    /**
     * Add Customer to SOAP map object
     *
     * @param LineItemInterface $object
     * @param \stdClass $map
     * @return \stdClass
     * @throws \Vertex\Exception\ValidationException
     */
    private function addCustomerToMap(LineItemInterface $object, \stdClass $map)
    {
        if ($object->getCustomer() !== null) {
            $map->Customer = $this->customerMapper->map($object->getCustomer());
        }
        return $map;
    }

    /**
     * Add the Delivery Term to the map
     *
     * @param LineItemInterface $object
     * @param \stdClass $map
     * @return \stdClass
     * @throws \Vertex\Exception\ValidationException
     */
    private function addDeliveryTermToMap(LineItemInterface $object, \stdClass $map)
    {
        $map = $this->utilities->addToMapWithEnumerationValidation(
            $map,
            $object->getDeliveryTerm(),
            'deliveryTerm',
            [
                DeliveryTerm::CFR,
                DeliveryTerm::CIF,
                DeliveryTerm::CIP,
                DeliveryTerm::CPT,
                DeliveryTerm::CUS,
                DeliveryTerm::DAF,
                DeliveryTerm::DAP,
                DeliveryTerm::DAT,
                DeliveryTerm::DDP,
                DeliveryTerm::DDU,
                DeliveryTerm::DEQ,
                DeliveryTerm::DES,
                DeliveryTerm::EXW,
                DeliveryTerm::FAS,
                DeliveryTerm::FCA,
                DeliveryTerm::FOB,
                DeliveryTerm::SUP
            ],
            true,
            'Delivery Term'
        );
        return $map;
    }

    /**
     * Add Product to SOAP map object
     *
     * @param LineItemInterface $object
     * @param \stdClass $map
     * @return \stdClass
     */
    private function addProductToMap(LineItemInterface $object, \stdClass $map)
    {
        if ($object->getProductCode() !== null) {
            $map->Product = new \stdClass();
            $map->Product->_ = $object->getProductCode();
        }
        if ($object->getProductClass() !== null) {
            $map->Product = isset($map->Product) ? $map->Product : new \stdClass();
            $map->Product->productClass = $object->getProductClass();
        }

        return $map;
    }

    /**
     * Add Seller to SOAP map object
     *
     * @param \stdClass $map
     * @param LineItemInterface $object
     * @return \stdClass
     * @throws \Vertex\Exception\ValidationException
     */
    private function addSellerToMap(\stdClass $map, LineItemInterface $object)
    {
        if ($object->getSeller() !== null) {
            $map->Seller = $this->sellerMapper->map($object->getSeller());
        }
        return $map;
    }

    /**
     * Map commodity code data into a LineItem
     *
     * @param \stdClass $map
     * @param LineItem $object
     * @return void
     */
    private function buildCommodityCode(\stdClass $map, LineItem $object)
    {
        if (isset($map->CommodityCode) && $map->CommodityCode instanceof \stdClass) {
            $object->setCommodityCode($map->CommodityCode->_);
            if (isset($map->CommodityCode->commodityCodeType)) {
                $object->setCommodityCodeType($map->CommodityCode->commodityCodeType);
            }
        }
    }

    /**
     * Map Flexible Code Fields
     *
     * @param \stdClass $mapFields Representation of <FlexibleFields> tag
     * @param FlexibleFieldInterface[] $flexibleFields
     * @return FlexibleFieldInterface[]
     */
    private function buildFlexibleCodeFields($mapFields, array $flexibleFields)
    {
        if (isset($mapFields->FlexibleCodeField)) {
            if ($mapFields->FlexibleCodeField instanceof \stdClass) {
                $flexibleFields[] = $this->flexibleCodeFieldMapper->build($mapFields->FlexibleCodeField);
            } else {
                foreach ($mapFields->FlexibleCodeField as $codeField) {
                    $flexibleFields[] = $this->flexibleCodeFieldMapper->build($codeField);
                }
            }
        }
        return $flexibleFields;
    }

    /**
     * Map Flexible Date Fields
     *
     * @param \stdClass $mapFields Representation of <FlexibleFields> tag
     * @param FlexibleFieldInterface[] $flexibleFields
     * @return FlexibleFieldInterface[]
     */
    private function buildFlexibleDateFields($mapFields, array $flexibleFields)
    {
        if (isset($mapFields->FlexibleDateField)) {
            if ($mapFields->FlexibleDateField instanceof \stdClass) {
                $flexibleFields[] = $this->flexibleDateFieldMapper->build($mapFields->FlexibleDateField);
            } else {
                foreach ($mapFields->FlexibleDateField as $dateField) {
                    $flexibleFields[] = $this->flexibleDateFieldMapper->build($dateField);
                }
            }
        }
        return $flexibleFields;
    }

    /**
     * Build out flexible field items from a LineItem stdClass
     *
     * @param \stdClass $map
     * @param LineItemInterface $object
     * @return void
     */
    private function buildFlexibleFields(\stdClass $map, LineItemInterface $object)
    {
        $flexibleFields = [];
        if (isset($map->FlexibleFields)) {
            $mapFields = $map->FlexibleFields;
            $flexibleFields = $this->buildFlexibleCodeFields($mapFields, $flexibleFields);
            $flexibleFields = $this->buildFlexibleDateFields($mapFields, $flexibleFields);
            $flexibleFields = $this->buildFlexibleNumericFields($mapFields, $flexibleFields);
        }

        $object->setFlexibleFields($flexibleFields);
    }

    /**
     * Map Flexible Numeric Fields
     *
     * @param \stdClass $mapFields Representation of <FlexibleFields> tag
     * @param FlexibleFieldInterface[] $flexibleFields
     * @return FlexibleFieldInterface[]
     */
    private function buildFlexibleNumericFields($mapFields, array $flexibleFields)
    {
        if (isset($mapFields->FlexibleNumericField)) {
            if ($mapFields->FlexibleNumericField instanceof \stdClass) {
                $flexibleFields[] = $this->flexibleNumericFieldMapper->build($mapFields->FlexibleNumericField);
            } else {
                foreach ($mapFields->FlexibleNumericField as $numField) {
                    $flexibleFields[] = $this->flexibleNumericFieldMapper->build($numField);
                }
            }
        }
        return $flexibleFields;
    }

    /**
     * Map product data into a LineItem
     *
     * @param \stdClass $map
     * @param LineItem $object
     * @return void
     */
    private function buildProduct(\stdClass $map, LineItem $object)
    {
        if (isset($map->Product)) {
            if ($map->Product instanceof \stdClass) {
                $object->setProductCode($map->Product->_);
            } else {
                $object->setProductCode($map->Product);
            }
            if (isset($map->Product->productClass)) {
                $object->setProductClass($map->Product->productClass);
            }
        }
    }

    /**
     * Map Flexible Fields
     *
     * @param LineItemInterface $object
     * @param \stdClass $map
     * @return void
     * @throws \Vertex\Exception\ValidationException
     */
    private function mapFlexibleFields(LineItemInterface $object, \stdClass $map)
    {
        foreach ($object->getFlexibleFields() as $flexibleField) {
            if ($flexibleField instanceof FlexibleCodeFieldInterface) {
                if (!isset($map->FlexibleFields->FlexibleCodeField)) {
                    $map->FlexibleFields->FlexibleCodeField = [];
                }
                $map->FlexibleFields->FlexibleCodeField[] = $this->flexibleCodeFieldMapper->map($flexibleField);
            }
            if ($flexibleField instanceof FlexibleDateFieldInterface) {
                if (!isset($map->FlexibleFields->FlexibleDateField)) {
                    $map->FlexibleFields->FlexibleDateField = [];
                }
                $map->FlexibleFields->FlexibleDateField[] = $this->flexibleDateFieldMapper->map($flexibleField);
            }
            if ($flexibleField instanceof FlexibleNumericFieldInterface) {
                if (!isset($map->FlexibleFields->FlexibleNumericField)) {
                    $map->FlexibleFields->FlexibleNumericField = [];
                }
                $map->FlexibleFields->FlexibleNumericField[] = $this->flexibleNumericFieldMapper->map($flexibleField);
            }
        }
    }
}
