<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLogging\Model\ResourceModel\LogEntry as ResourceModel;

/**
 * Data model for a Log Entry
 */
class LogEntry extends AbstractModel implements LogEntryInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getDate(): string
    {
        return $this->getData(static::FIELD_REQUEST_DATE);
    }

    public function getId(): ?int
    {
        return $this->getData(static::FIELD_ID) !== null ? (int)$this->getData(static::FIELD_ID) : null;
    }

    public function getLookupResult(): string
    {
        return $this->getData(static::FIELD_LOOKUP_RESULT);
    }

    public function getModuleName(): ?string
    {
        return $this->getData(static::FIELD_MODULE_NAME);
    }

    public function getModuleVersion(): ?string
    {
        return $this->getData(static::FIELD_MODULE_VERSION);
    }

    public function getOrderId(): ?int
    {
        return $this->getData(static::FIELD_ORDER_ID) !== null ? (int)$this->getData(static::FIELD_ORDER_ID) : null;
    }

    public function getRequestXml(): ?string
    {
        return $this->getData(static::FIELD_REQUEST_XML);
    }

    public function getResponseTime(): ?int
    {
        return $this->getData(static::FIELD_RESPONSE_TIME) !== null
            ? (int)$this->getData(static::FIELD_RESPONSE_TIME)
            : null;
    }

    public function getResponseXml(): ?string
    {
        return $this->getData(static::FIELD_RESPONSE_XML);
    }

    public function getSubTotal(): ?float
    {
        return $this->getData(static::FIELD_SUBTOTAL) !== null ? (float)$this->getData(static::FIELD_SUBTOTAL) : null;
    }

    public function getTaxAreaId(): ?int
    {
        return $this->getData(static::FIELD_TAX_AREA_ID) !== null
            ? (int)$this->getData(static::FIELD_TAX_AREA_ID)
            : null;
    }

    public function getTotal(): ?float
    {
        return $this->getData(static::FIELD_TOTAL) !== null ? (float)$this->getData(static::FIELD_TOTAL) : null;
    }

    public function getTotalTax(): ?float
    {
        return $this->getData(static::FIELD_TOTAL_TAX) !== null ? (float)$this->getData(static::FIELD_TOTAL_TAX) : null;
    }

    public function getType(): string
    {
        return $this->getData(static::FIELD_TYPE);
    }

    public function setDate(string $requestDate): LogEntryInterface
    {
        return $this->setData(static::FIELD_REQUEST_DATE, $requestDate);
    }

    public function setId($requestId): LogEntryInterface
    {
        return $this->setData(static::FIELD_ID, $requestId !== null ? (int)$requestId : null);
    }

    public function setLookupResult(string $lookupResult): LogEntryInterface
    {
        return $this->setData(static::FIELD_LOOKUP_RESULT, $lookupResult);
    }

    public function setModuleName(?string $moduleName): LogEntryInterface
    {
        return $this->setData(static::FIELD_MODULE_NAME, $moduleName);
    }

    public function setModuleVersion(?string $moduleVersion): LogEntryInterface
    {
        return $this->setData(static::FIELD_MODULE_VERSION, $moduleVersion);
    }

    public function setOrderId(int $orderId): LogEntryInterface
    {
        return $this->setData(static::FIELD_ORDER_ID, $orderId);
    }

    public function setRequestXml(string $requestXml): LogEntryInterface
    {
        return $this->setData(static::FIELD_REQUEST_XML, $requestXml);
    }

    public function setResponseTime(int $milliseconds): LogEntryInterface
    {
        return $this->setData(static::FIELD_RESPONSE_TIME, $milliseconds);
    }

    public function setResponseXml(string $responseXml): LogEntryInterface
    {
        return $this->setData(static::FIELD_RESPONSE_XML, $responseXml);
    }

    public function setSubTotal(float $subtotal): LogEntryInterface
    {
        return $this->setData(static::FIELD_SUBTOTAL, $subtotal);
    }

    public function setTaxAreaId(int $taxAreaId): LogEntryInterface
    {
        return $this->setData(static::FIELD_TAX_AREA_ID, $taxAreaId);
    }

    public function setTotal(float $total): LogEntryInterface
    {
        return $this->setData(static::FIELD_TOTAL, $total);
    }

    public function setTotalTax(float $totalTax): LogEntryInterface
    {
        return $this->setData(static::FIELD_TOTAL_TAX, $totalTax);
    }

    public function setType(string $type): LogEntryInterface
    {
        return $this->setData(static::FIELD_TYPE, $type);
    }
}
