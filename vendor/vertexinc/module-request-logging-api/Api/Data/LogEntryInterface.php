<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLoggingApi\Api\Data;

/**
 * Data model representing an entry in the Vertex API Log
 *
 * @api
 */
interface LogEntryInterface
{
    const FIELD_CART_ID = 'quote_id';
    const FIELD_ID = 'request_id';
    const FIELD_LOOKUP_RESULT = 'lookup_result';
    const FIELD_MODULE_NAME = 'module_name';
    const FIELD_MODULE_VERSION = 'module_version';
    const FIELD_ORDER_ID = 'order_id';
    const FIELD_REQUEST_DATE = 'request_date';
    const FIELD_REQUEST_XML = 'request_xml';
    const FIELD_RESPONSE_TIME = 'response_time';
    const FIELD_RESPONSE_XML = 'response_xml';
    const FIELD_SOURCE_PATH = 'source_path';
    const FIELD_SUBTOTAL = 'sub_total';
    const FIELD_TAX_AREA_ID = 'tax_area_id';
    const FIELD_TOTAL = 'total';
    const FIELD_TOTAL_TAX = 'total_tax';
    const FIELD_TYPE = 'request_type';

    /**
     * Get the date of the request
     *
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * Retrieve unique identifier for the Log Entry
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get the result of the lookup
     *
     * Typically empty, the string "NORMAL" or a SOAP Exception
     *
     * @return string|null
     */
    public function getLookupResult(): ?string;

    /**
     * Get the module name of the request
     *
     * @return string|null
     */
    public function getModuleName(): ?string;

    /**
     * Get the module version of the request
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string;

    /**
     * Get the ID of the Order the request was made for
     *
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * Get the XML sent to the Vertex API
     *
     * @return string|null
     */
    public function getRequestXml(): ?string;

    /**
     * Return the time taken to get a response in milliseconds
     *
     * @return int|null
     */
    public function getResponseTime(): ?int;

    /**
     * Get the XML response received from the Vertex API
     *
     * @return string|null
     */
    public function getResponseXml(): ?string;

    /**
     * Get the total of the request before taxes
     *
     * @return float|null
     */
    public function getSubTotal(): ?float;

    /**
     * Get the Tax Area ID calculated by the request
     *
     * @return string|null
     */
    public function getTaxAreaId(): ?int;

    /**
     * Get the total of the request after taxes
     *
     * @return float|null
     */
    public function getTotal(): ?float;

    /**
     * Get the total amount of tax calculated by the request
     *
     * @return float|null
     */
    public function getTotalTax(): ?float;

    /**
     * Get the type of request
     *
     * Typically one of quote, invoice, tax_area_lookup or creditmemo
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set the date of the request
     *
     * @param string $requestDate Date in format of Y-m-d H:i:s
     * @return LogEntryInterface
     */
    public function setDate(string $requestDate): self;

    /**
     * Set unique identifier for the Log Entry
     *
     * @param int $requestId
     * @return LogEntryInterface
     */
    public function setId(int $requestId): self;

    /**
     * Set the result of the lookup
     *
     * Typically empty, the string "NORMAL" or a SOAP Exception
     *
     * @param string $lookupResult
     * @return LogEntryInterface
     */
    public function setLookupResult(string $lookupResult): self;

    /**
     * Set the module name of the request
     *
     * @param string|null $moduleName
     * @return LogEntryInterface
     */
    public function setModuleName(?string $moduleName): self;

    /**
     * Set the module version of the request
     *
     * @param string|null $moduleVersion
     * @return LogEntryInterface
     */
    public function setModuleVersion(?string $moduleVersion): self;

    /**
     * Set the ID of the Order the request was made for
     *
     * @param int $orderId
     * @return LogEntryInterface
     */
    public function setOrderId(int $orderId): self;

    /**
     * Set the XML sent to the Vertex API
     *
     * @param string $requestXml
     * @return LogEntryInterface
     */
    public function setRequestXml(string $requestXml): self;

    /**
     * Set the time taken to get a response in milliseconds
     *
     * @param int $milliseconds
     * @return LogEntryInterface
     */
    public function setResponseTime(int $milliseconds): self;

    /**
     * Set the XML response received from the Vertex API
     *
     * @param string $responseXml
     * @return LogEntryInterface
     */
    public function setResponseXml(string $responseXml): self;

    /**
     * Set the total of the request before taxes
     *
     * @param float $subtotal
     * @return LogEntryInterface
     */
    public function setSubTotal(float $subtotal): self;

    /**
     * Set the Tax Area ID calculated by the request
     *
     * @param int $taxAreaId
     * @return LogEntryInterface
     */
    public function setTaxAreaId(int $taxAreaId): self;

    /**
     * Set the total of the request after taxes
     *
     * @param float $total
     * @return LogEntryInterface
     */
    public function setTotal(float $total): self;

    /**
     * Set the total amount of tax calculated by the request
     *
     * @param float $totalTax
     * @return LogEntryInterface
     */
    public function setTotalTax(float $totalTax): self;

    /**
     * Set the type of request
     *
     * Typically one of quote, invoice, tax_area_lookup or creditmemo
     *
     * @param string $type
     * @return LogEntryInterface
     */
    public function setType(string $type): self;
}
