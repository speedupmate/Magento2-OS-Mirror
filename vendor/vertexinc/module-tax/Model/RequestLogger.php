<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterfaceFactory;
use Vertex\RequestLoggingApi\Model\LogRequestInterface;

/**
 * Performs all the actions necessary for logging a request
 */
class RequestLogger
{
    /** @var DateTime */
    private $dateTime;

    /** @var DomDocumentFactory */
    private $documentFactory;

    /** @var LogEntryInterfaceFactory */
    private $factory;

    /** @var LogRequestInterface */
    private $logRequest;

    /** @var ModuleDetail */
    private $moduleDetail;

    /** @var TimezoneInterface */
    private $timezone;

    public function __construct(
        LogEntryInterfaceFactory $logEntryFactory,
        LogRequestInterface $logRequest,
        DateTime $dateTime,
        DomDocumentFactory $documentFactory,
        ModuleDetail $moduleDetail,
        TimezoneInterface $timezone
    ) {
        $this->factory = $logEntryFactory;
        $this->logRequest = $logRequest;
        $this->dateTime = $dateTime;
        $this->documentFactory = $documentFactory;
        $this->moduleDetail = $moduleDetail;
        $this->timezone = $timezone;
    }

    /**
     * Log a Request
     *
     * @param string $type
     * @param string $requestXml
     * @param string $responseXml
     * @param int|null $responseTime
     * @return void
     */
    public function log($type, $requestXml, $responseXml, $logLevel, $responseTime = null)
    {
        $logEntry = $this->factory->create();
        $dateTime = $this->timezone->scopeDate(null, time(), true);
        $timestamp = $this->dateTime->formatDate($dateTime);
        $logEntry->setType($type);
        $logEntry->setDate($timestamp);

        if ($responseTime !== null) {
            $logEntry->setResponseTime($responseTime);
        }

        $logEntry->setModuleName($this->moduleDetail->getModuleName());
        $logEntry->setModuleVersion($this->moduleDetail->getModuleVersion());
        $logEntry->setResponseXml($responseXml);
        $logEntry->setRequestXml($requestXml);

        $this->addResponseDataToLogEntry($logEntry, $responseXml);
        $this->logRequest->execute($logEntry, $logLevel);
    }

    /**
     * Add data from the response XML to the LogEntry
     *
     * @param LogEntryInterface $logEntry
     * @param string $responseXml
     * @return LogEntryInterface
     */
    private function addResponseDataToLogEntry(LogEntryInterface $logEntry, $responseXml)
    {
        $dom = $this->documentFactory->create();

        if (!empty($responseXml)) {
            $dom->loadXML($responseXml);

            $totalTaxNodes = $dom->getElementsByTagName('TotalTax');
            $totalTaxNode = null;
            for ($i = 0; $i < $totalTaxNodes->length; ++$i) {
                if ($totalTaxNodes->item($i)->parentNode->localName === 'QuotationResponse' ||
                    $totalTaxNodes->item($i)->parentNode->localName === 'InvoiceResponse') {
                    $totalTaxNode = $totalTaxNodes->item($i);
                    break;
                }
            }
            $totalNode = $dom->getElementsByTagName('Total');
            $subtotalNode = $dom->getElementsByTagName('SubTotal');
            $lookupResultNode = $dom->getElementsByTagName('Status');
            $addressLookupFaultNode = $dom->getElementsByTagName('exceptionType');
            $total = $totalNode->length > 0 ? $totalNode->item(0)->nodeValue : 0;
            $subtotal = $subtotalNode->length > 0 ? $subtotalNode->item(0)->nodeValue : 0;
            $totalTax = $totalTaxNode !== null ? $totalTaxNode->nodeValue : 0;

            $lookupResult = '';
            if ($lookupResultNode->length > 0) {
                $lookupResult = $lookupResultNode->item(0)->getAttribute('lookupResult');
            } elseif ($addressLookupFaultNode->length > 0) {
                $lookupResult = $addressLookupFaultNode->item(0)->nodeValue;
            }

            $logEntry->setTotalTax((float)$totalTax);
            $logEntry->setTotal((float)$total);
            $logEntry->setSubTotal((float)$subtotal);
            $logEntry->setLookupResult($lookupResult);
        }

        return $logEntry;
    }
}
