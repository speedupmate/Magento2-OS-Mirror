<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Api\Data;

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
     * @return string
     */
    public function getDate();

    /**
     * Retrieve unique identifier for the Log Entry
     *
     * @return int
     */
    public function getId();

    /**
     * Get the result of the lookup
     *
     * Typically empty, the string "NORMAL" or a SOAP Exception
     *
     * @return string
     */
    public function getLookupResult();

    /**
     * Get the ID of the Order the request was made for
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Get the XML sent to the Vertex API
     *
     * @return string
     */
    public function getRequestXml();

    /**
     * Return the time taken to get a response in milliseconds
     *
     * @return int
     */
    public function getResponseTime();

    /**
     * Get the XML response received from the Vertex API
     *
     * @return string
     */
    public function getResponseXml();

    /**
     * Get the total of the request before taxes
     *
     * @return float
     */
    public function getSubTotal();

    /**
     * Get the Tax Area ID calculated by the request
     *
     * @return int
     */
    public function getTaxAreaId();

    /**
     * Get the total of the request after taxes
     *
     * @return float
     */
    public function getTotal();

    /**
     * Get the total amount of tax calculated by the request
     *
     * @return float
     */
    public function getTotalTax();

    /**
     * Get the type of request
     *
     * Typically one of quote, invoice, tax_area_lookup or creditmemo
     *
     * @return string
     */
    public function getType();

    /**
     * Set the date of the request
     *
     * @param string $requestDate Date in format of Y-m-d H:i:s
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setDate($requestDate);

    /**
     * Set unique identifier for the Log Entry
     *
     * @param int $requestId
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setId($requestId);

    /**
     * Set the result of the lookup
     *
     * Typically empty, the string "NORMAL" or a SOAP Exception
     *
     * @param string $lookupResult
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setLookupResult($lookupResult);

    /**
     * Set the ID of the Order the request was made for
     *
     * @param int $orderId
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setOrderId($orderId);

    /**
     * Set the XML sent to the Vertex API
     *
     * @param string $requestXml
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setRequestXml($requestXml);

    /**
     * Set the time taken to get a response in milliseconds
     *
     * @param int $milliseconds
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setResponseTime($milliseconds);

    /**
     * Set the XML response received from the Vertex API
     *
     * @param string $responseXml
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setResponseXml($responseXml);

    /**
     * Set the total of the request before taxes
     *
     * @param float $subtotal
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setSubTotal($subtotal);

    /**
     * Set the Tax Area ID calculated by the request
     *
     * @param int $taxAreaId
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setTaxAreaId($taxAreaId);

    /**
     * Set the total of the request after taxes
     *
     * @param float $total
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setTotal($total);

    /**
     * Set the total amount of tax calculated by the request
     *
     * @param float $totalTax
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setTotalTax($totalTax);

    /**
     * Set the type of request
     *
     * Typically one of quote, invoice, tax_area_lookup or creditmemo
     *
     * @param string $type
     * @return \Vertex\Tax\Api\Data\LogEntryInterface
     */
    public function setType($type);
}
