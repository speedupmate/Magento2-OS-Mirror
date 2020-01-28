<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Vertex\Services\SoapCallResponseInterface;
use Vertex\Tax\Model\Api\Utility\SoapClientRegistry;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\RequestLogger;

/**
 * Contains functionality for logging API calls
 */
class Logger
{
    /** @var Config */
    private $config;

    /** @var ExceptionLogger */
    private $logger;

    /** @var RequestLogger */
    private $requestLogger;

    /** @var SoapClientRegistry */
    private $soapClientRegistry;

    /**
     * @param ExceptionLogger $logger
     * @param RequestLogger $requestLogger
     * @param SoapClientRegistry $soapClientRegistry
     * @param Config $config
     */
    public function __construct(
        ExceptionLogger $logger,
        RequestLogger $requestLogger,
        SoapClientRegistry $soapClientRegistry,
        Config $config
    ) {
        $this->logger = $logger;
        $this->requestLogger = $requestLogger;
        $this->soapClientRegistry = $soapClientRegistry;
        $this->config = $config;
    }

    /**
     * Wrap an API call to ensure it is logged
     *
     * @param callable $callable
     * @param string $type
     * @param string|null $scopeCode Store ID
     * @return mixed Result of callable
     * @throws \Exception
     */
    public function wrapCall(callable $callable, $type, $scopeCode = null)
    {
        $result = null;
        try {
            $result = $callable();
            return $result;
        } catch (\Exception $exception) {
            $this->logException($exception);
            throw $exception;
        } finally {
            $this->logRequest($type, $result, $scopeCode);
        }
    }

    /**
     * Log an Exception
     *
     * @param \Exception $exception
     * @return void
     */
    private function logException(\Exception $exception)
    {
        $this->logger->critical($exception);
    }

    /**
     * Log an API call to the database
     *
     * @param string $requestType
     * @param mixed $result
     * @param string|null $scopeCode Store ID
     * @return void
     */
    private function logRequest($requestType, $result, $scopeCode = null)
    {
        if (!$this->config->isLoggingEnabled($scopeCode)) {
            return;
        }

        $responseTime = $result instanceof SoapCallResponseInterface ? $result->getHttpCallTime() : null;

        $soapClient = $this->soapClientRegistry->getLastClient();
        try {
            $this->requestLogger->log(
                $requestType,
                $soapClient ? $soapClient->__getLastRequest() : null,
                $soapClient ? $soapClient->__getLastResponse() : null,
                $responseTime
            );
        } catch (CouldNotSaveException $originalException) {
            $loggedException = new \Exception('Failed to log Vertex Request', 0, $originalException);
            $this->logException($loggedException);
        }
    }
}
