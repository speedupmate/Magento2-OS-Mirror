<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLoggingApi\Api\LogEntryRepositoryInterface;
use Vertex\RequestLoggingApi\Model\LogRequestInterface;
use Vertex\RequestLoggingApi\Model\RetrieveLogLevelInterface;

class LogRequest implements LogRequestInterface
{
    /**
     * @var DomDocumentFactory
     */
    private $documentFactory;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $repository;

    /**
     * @var RetrieveLogLevelInterface
     */
    private $retrieveLogLevel;

    public function __construct(
        DomDocumentFactory $documentFactory,
        LogEntryRepositoryInterface $repository,
        RetrieveLogLevelInterface $retrieveLogLevel
    ) {
        $this->documentFactory = $documentFactory;
        $this->repository = $repository;
        $this->retrieveLogLevel = $retrieveLogLevel;
    }

    /**
     * Format the XML and Forward LogEntryInterface to LogEntryRepository
     *
     * @throws CouldNotSaveException
     */
    public function execute(
        LogEntryInterface $logEntry,
        #[ExpectedValues(valuesFromClass: RetrieveLogLevelInterface::class)]
        int $logLevel = RetrieveLogLevelInterface::LEVEL_TRACE
    ): bool {
        if ($this->retrieveLogLevel->execute() < $logLevel) {
            return false;
        }

        $requestXml = $logEntry->getRequestXml();
        if ($requestXml !== null) {
            $requestXml = $this->formatXml($requestXml);
            $logEntry->setRequestXml($requestXml);
        }

        $responseXml = $logEntry->getResponseXml();
        if ($responseXml !== null) {
            $responseXml = $this->formatXml($responseXml);
            $logEntry->setResponseXml($responseXml);
        }

        $this->repository->save($logEntry);
        return true;
    }

    /**
     * Format a string of XML
     */
    private function formatXml(string $xml): string
    {
        if (empty($xml)) {
            return '';
        }

        $dom = $this->documentFactory->create();

        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        // Secure TrustedId
        $trustedId = $dom->getElementsByTagName('TrustedId');
        if ($trustedId->length) {
            $trustedId->item(0)->textContent = '*****';
        }

        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
