<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model\Data;

use Magento\Framework\DataObject;
use Vertex\Tax\Api\Data\LogEntryInterface;

/**
 * Data model for a Log Entry
 */
class LogEntry extends DataObject implements LogEntryInterface
{
    public function getCartId()
    {
        return $this->getData(static::FIELD_CART_ID);
    }

    public function getDate()
    {
        return $this->getData(static::FIELD_REQUEST_DATE);
    }

    public function getId()
    {
        return $this->getData(static::FIELD_ID);
    }

    public function getLookupResult()
    {
        return $this->getData(static::FIELD_LOOKUP_RESULT);
    }

    public function getOrderId()
    {
        return $this->getData(static::FIELD_ORDER_ID);
    }

    public function getRequestXml()
    {
        return $this->getData(static::FIELD_REQUEST_XML);
    }

    public function getResponseTime()
    {
        return $this->getData(static::FIELD_RESPONSE_TIME);
    }

    public function getResponseXml()
    {
        return $this->getData(static::FIELD_RESPONSE_XML);
    }

    public function getSourcePath()
    {
        return $this->getData(static::FIELD_SOURCE_PATH);
    }

    public function getSubTotal()
    {
        return $this->getData(static::FIELD_SUBTOTAL);
    }

    public function getTaxAreaId()
    {
        return $this->getData(static::FIELD_TAX_AREA_ID);
    }

    public function getTotal()
    {
        return $this->getData(static::FIELD_TOTAL);
    }

    public function getTotalTax()
    {
        return $this->getData(static::FIELD_TOTAL_TAX);
    }

    public function getType()
    {
        return $this->getData(static::FIELD_TYPE);
    }

    public function setCartId($cartId)
    {
        return $this->setData(static::FIELD_CART_ID, $cartId);
    }

    public function setDate($requestDate)
    {
        return $this->setData(static::FIELD_REQUEST_DATE, $requestDate);
    }

    public function setId($id)
    {
        return $this->setData(static::FIELD_ID, $id);
    }

    public function setLookupResult($lookupResult)
    {
        return $this->setData(static::FIELD_LOOKUP_RESULT, $lookupResult);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(static::FIELD_ORDER_ID, $orderId);
    }

    public function setRequestXml($requestXml)
    {
        return $this->setData(static::FIELD_REQUEST_XML, $requestXml);
    }

    public function setResponseTime($milliseconds)
    {
        return $this->setData(static::FIELD_RESPONSE_TIME, $milliseconds);
    }

    public function setResponseXml($responseXml)
    {
        return $this->setData(static::FIELD_RESPONSE_XML, $responseXml);
    }

    public function setSourcePath($sourcePath)
    {
        return $this->setData(static::FIELD_SOURCE_PATH, $sourcePath);
    }

    public function setSubTotal($subtotal)
    {
        return $this->setData(static::FIELD_SUBTOTAL, $subtotal);
    }

    public function setTaxAreaId($taxAreaId)
    {
        return $this->setData(static::FIELD_TAX_AREA_ID, $taxAreaId);
    }

    public function setTotal($total)
    {
        return $this->setData(static::FIELD_TOTAL, $total);
    }

    public function setTotalTax($totalTax)
    {
        return $this->setData(static::FIELD_TOTAL_TAX, $totalTax);
    }

    public function setType($type)
    {
        return $this->setData(static::FIELD_TYPE, $type);
    }
}
