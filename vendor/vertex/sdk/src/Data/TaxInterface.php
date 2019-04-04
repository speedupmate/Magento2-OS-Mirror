<?php

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Data;

/**
 * Represents a Tax on an Item
 *
 * @api
 */
interface TaxInterface
{
    /** Purchaser of the item taxes apply to */
    const PARTY_BUYER = 'BUYER';

    /** Seller of the item taxes apply to */
    const PARTY_SELLER = 'SELLER';

    /** Tax is payable at a later time */
    const RESULT_DEFERRED = 'DEFERRED';

    /** A direct payment permit has been applied */
    const RESULT_DPPAPPLIED = 'DPPAPPLIED';

    /** The item is exempt from taxation */
    const RESULT_EXEMPT = 'EXEMPT';

    /** The item is non-taxable */
    const RESULT_NONTAXABLE = 'NONTAXABLE';

    /** The item has no applicable taxes */
    const RESULT_NO_TAX = 'NO_TAX';

    /** The item is taxable */
    const RESULT_TAXABLE = 'TAXABLE';

    /** The tax is a Consumer Use tax */
    const TYPE_CONSUMERS_USE = 'CONSUMERS_USE';

    /** For VAT purposes, the tax is an import */
    const TYPE_IMPORT = 'IMPORT';

    /** The tax is an Import VAT tax */
    const TYPE_IMPORT_VAT = 'IMPORT_VAT';

    /** For VAT purposes, the tax is an input */
    const TYPE_INPUT = 'INPUT';

    /** For VAT purposes, the tax is an input/output */
    const TYPE_INPUT_OUTPUT = 'INPUT_OUTPUT';

    /** There is not a tax */
    const TYPE_NONE = 'NONE';

    /** For VAT purposes, the tax is an output */
    const TYPE_OUTPUT = 'OUTPUT';

    /** The tax is a Sales Tax */
    const TYPE_SALES = 'SALES';

    /** The tax is a Seller Use tax */
    const TYPE_SELLER_USE = 'SELLER_USE';

    /** The tax is a VAT tax */
    const TYPE_VAT = 'VAT';

    /** The invoice tax code for export */
    const INVOICE_TEXT_EXPORT = 1;

    /** The invoice tax code for Europe Intracommunity */
    const INVOICE_TEXT_INTRA_EU = 2;

    /** The invoice tax code for zero rate */
    const INVOICE_TEXT_ZERO_RATE = 3;

    /** The invoice tax code for reverse charge */
    const INVOICE_TEXT_REVERSE_CHARGE = 4;

    /** The invoice tax code for indeterminate tax*/
    const INVOICE_TEXT_INDETERMINATE_TAX = 5;

    /** The invoice tax code for triangulation */
    const INVOICE_TEXT_TRIANGULATION = 6;

    /** The invoice tax code for registration group */
    const INVOICE_TEXT_REGISTRATION_GROUP = 7;

    /** The invoice tax code for net exporter */
    const INVOICE_TEXT_NET_EXPORTER = 8;

    /** The invoice tax code for import */
    const INVOICE_TEXT_IMPORT_VAT = 9;

    /** The invoice tax code for out of scope PO */
    const INVOICE_TEXT_OUT_OF_SCOPE_PO = 10;

    /** The invoice tax code for out of scope AO */
    const INVOICE_TEXT_OUT_OF_SCOPE_AO = 11;

    /** The invoice tax code for suspended */
    const INVOICE_TEXT_SUSPENDED_VAT = 12;

    /** The invoice tax code for call off simplification */
    const INVOICE_TEXT_CALL_OFF_SIMPLIFICATION = 13;

    /** The invoice tax code for cosignment simplification */
    const INVOICE_TEXT_COSIGNMENT_SIMPLIFICATION = 14;

    /** (VAT Liability might exist at AO) */
    /** The invoice tax code for VAT liability AO */
    const INVOICE_TEXT_VAT_LIABILITY_AO = 15;

    /** The invoice tax code for VAT liability PO */
    const INVOICE_TEXT_VAT_LIABILITY_PO = 16;

    /** The invoice tax code for VAT liability AD */
    const INVOICE_TEXT_VAT_LIABILITY_AD = 17;

    /** The invoice tax code for VAT liability D */
    const INVOICE_TEXT_VAT_LIABILITY_D = 18;

    /** The invoice tax code for self billing */
    const INVOICE_TEXT_SELF_BILLING = 19;

    /** The invoice tax code for ESS */
    const INVOICE_TEXT_ESS = 20;

    /** The invoice tax code for intra country */
    const INVOICE_TEXT_INTRA_COUNTRY = 21;

    /** The invoice tax code for differential VAT */
    const INVOICE_TEXT_DIFFERENTIAL_VAT = 22;

    /** The invoice tax code for vat with holding */
    const INVOICE_TEXT_VAT_WITHHOLDING = 23;

    /** The invoice tax code for signpore sec 21 */
    const INVOICE_TEXT_SIGNAPORE_SEC_21 = 24;

    /** The invoice tax code for out of scope D */
    const INVOICE_TEXT_OUT_OF_SCOPE_D = 25;

    /** All Invoice text codes */
    const INVOICE_TEXT_CODES = [
        self::INVOICE_TEXT_EXPORT,
        self::INVOICE_TEXT_INTRA_EU,
        self::INVOICE_TEXT_ZERO_RATE,
        self::INVOICE_TEXT_REVERSE_CHARGE,
        self::INVOICE_TEXT_INDETERMINATE_TAX,
        self::INVOICE_TEXT_TRIANGULATION,
        self::INVOICE_TEXT_REGISTRATION_GROUP,
        self::INVOICE_TEXT_NET_EXPORTER,
        self::INVOICE_TEXT_IMPORT_VAT,
        self::INVOICE_TEXT_OUT_OF_SCOPE_PO,
        self::INVOICE_TEXT_OUT_OF_SCOPE_AO,
        self::INVOICE_TEXT_SUSPENDED_VAT,
        self::INVOICE_TEXT_CALL_OFF_SIMPLIFICATION,
        self::INVOICE_TEXT_COSIGNMENT_SIMPLIFICATION,
        self::INVOICE_TEXT_VAT_LIABILITY_AO,
        self::INVOICE_TEXT_VAT_LIABILITY_PO,
        self::INVOICE_TEXT_VAT_LIABILITY_AD,
        self::INVOICE_TEXT_VAT_LIABILITY_D,
        self::INVOICE_TEXT_SELF_BILLING,
        self::INVOICE_TEXT_ESS,
        self::INVOICE_TEXT_INTRA_COUNTRY,
        self::INVOICE_TEXT_DIFFERENTIAL_VAT,
        self::INVOICE_TEXT_VAT_WITHHOLDING,
        self::INVOICE_TEXT_SIGNAPORE_SEC_21,
        self::INVOICE_TEXT_OUT_OF_SCOPE_D,
    ];

    /** All tax results */
    const TAX_RESULTS = [
        self::RESULT_TAXABLE,
        self::RESULT_NONTAXABLE,
        self::RESULT_EXEMPT,
        self::RESULT_DPPAPPLIED,
        self::RESULT_NO_TAX,
        self::RESULT_DEFERRED,
    ];

    /** All Tax Types */
    const TAX_TYPES = [
        self::TYPE_SALES,
        self::TYPE_SELLER_USE,
        self::TYPE_CONSUMERS_USE,
        self::TYPE_VAT,
        self::TYPE_IMPORT_VAT,
        self::TYPE_NONE,
    ];

    /**
     * Retrieve the amount of tax calculated by Vertex
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * Retrieve the party tax should be collected from
     *
     * If the party is seller, the tax cannot be charged to the customer and should not be written to the invoice
     *
     * @see TaxInterface::PARTY_BUYER
     * @see TaxInterface::PARTY_SELLER
     * @return string|null
     */
    public function getCollectedFromParty();

    /**
     * Retrieve the effective rate of the tax
     *
     * For Buyer Input tax and Seller Import tax, this rate is calculated based on the Extended Price and Tax Amount
     * (Import or Input) passed in the Request message. For all other message types, this is the effective rate the
     * system used to calculate tax.
     *
     * @return float|null
     */
    public function getEffectiveRate();

    /**
     * Retrieve the Tax Imposition Name
     *
     * @return string|null
     */
    public function getImposition();

    /**
     * Retrieve the Tax Imposition Type
     *
     * @return string|null
     */
    public function getImpositionType();

    /**
     * Retrieve the Input/Output VAT type
     *
     * Identifies whether tax is Input VAT, Output VAT, or both according to the perspective of the transaction to allow
     * for Reverse Charges.
     *
     * @see TaxInterface::TYPE_IMPORT
     * @see TaxInterface::TYPE_INPUT
     * @see TaxInterface::TYPE_INPUT_OUTPUT
     * @see TaxInterface::TYPE_OUTPUT
     * @return string|null
     */
    public function getInputOutputType();

    /**
     * Retrieve the Jurisdiction that levied the tax
     *
     * @return JurisdictionInterface|null
     */
    public function getJurisdiction();

    /**
     * Retrieve the result of the tax request
     *
     * @see TaxInterface::RESULT_DEFERRED
     * @see TaxInterface::RESULT_DPPAPPLIED
     * @see TaxInterface::RESULT_EXEMPT
     * @see TaxInterface::RESULT_NO_TAX
     * @see TaxInterface::RESULT_NONTAXABLE
     * @see TaxInterface::RESULT_TAXABLE
     * @return string|null
     */
    public function getResult();

    /**
     * Retrieve the type of tax levied
     *
     * @see TaxInterface::TYPE_CONSUMERS_USE
     * @see TaxInterface::TYPE_IMPORT_VAT
     * @see TaxInterface::TYPE_NONE
     * @see TaxInterface::TYPE_SALES
     * @see TaxInterface::TYPE_SELLER_USE
     * @see TaxInterface::TYPE_VAT
     * @return string|null
     */
    public function getType();

    /**
     * Retrieve invoice text codes
     *
     * @return int[]|null
     */
    public function getInvoiceTextCodes();

    /**
     * Retrieve tax code
     *
     * @return string|null
     */
    public function getTaxCode();

    /**
     * Retrieve Vertex tax code
     *
     * @return string|null
     */
    public function getVertexTaxCode();

    /**
     * Set the amount of tax calculated by Vertex
     *
     * @param float $calculatedTax
     * @return TaxInterface
     */
    public function setAmount($calculatedTax);

    /**
     * Set the party tax should be collected from
     *
     * If the party is seller, the tax cannot be charged to the customer and should not be written to the invoice
     *
     * @see TaxInterface::PARTY_BUYER
     * @see TaxInterface::PARTY_SELLER
     * @param string $party
     * @return TaxInterface
     */
    public function setCollectedFromParty($party);

    /**
     * Set the effective rate of the tax
     *
     * For Buyer Input tax and Seller Import tax, this rate is calculated based on the Extended Price and Tax Amount
     * (Import or Input) passed in the Request message. For all other message types, this is the effective rate the
     * system used to calculate tax.
     *
     * @param float $effectiveRate
     * @return TaxInterface
     */
    public function setEffectiveRate($effectiveRate);

    /**
     * Set the Tax Imposition Name
     *
     * @param string $imposition
     * @return TaxInterface
     */
    public function setImposition($imposition);

    /**
     * Set the Tax Imposition Type
     *
     * @param string $impositionType
     * @return string
     */
    public function setImpositionType($impositionType);

    /**
     * Set the Input/Output VAT type
     *
     * Identifies whether tax is Input VAT, Output VAT, or both according to the perspective of the transaction to allow
     * for Reverse Charges.
     *
     * @see TaxInterface::TYPE_IMPORT
     * @see TaxInterface::TYPE_INPUT
     * @see TaxInterface::TYPE_INPUT_OUTPUT
     * @see TaxInterface::TYPE_OUTPUT
     * @param string $type
     * @return TaxInterface
     */
    public function setInputOutputType($type);

    /**
     * Set the Jurisdiction that levied the tax
     *
     * @param JurisdictionInterface
     * @return TaxInterface
     */
    public function setJurisdiction($jurisdiction);

    /**
     * Set the result of the tax request
     *
     * @see TaxInterface::RESULT_DEFERRED
     * @see TaxInterface::RESULT_DPPAPPLIED
     * @see TaxInterface::RESULT_EXEMPT
     * @see TaxInterface::RESULT_NO_TAX
     * @see TaxInterface::RESULT_NONTAXABLE
     * @see TaxInterface::RESULT_TAXABLE
     * @param string $result
     * @return TaxInterface
     */
    public function setResult($result);

    /**
     * Set the type of tax levied
     *
     * @see TaxInterface::TYPE_CONSUMERS_USE
     * @see TaxInterface::TYPE_IMPORT_VAT
     * @see TaxInterface::TYPE_NONE
     * @see TaxInterface::TYPE_SALES
     * @see TaxInterface::TYPE_SELLER_USE
     * @see TaxInterface::TYPE_VAT
     * @param string $type
     * @return TaxInterface
     */
    public function setType($type);

    /**
     * Set text codes for invoices
     *
     * @param int[] $taxCodes
     * @return TaxInterface
     */
    public function setInvoiceTextCodes($taxCodes);

    /**
     * Set tax code
     *
     * @param string $code
     * @return TaxInterface
     */
    public function setTaxCode($code);

    /**
     * Set Vertex tax code
     *
     * @param string $code
     * @return TaxInterface
     */
    public function setVertexTaxCode($code);
}
