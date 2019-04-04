<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Temando\Shipping\Rest\Adapter\BatchApiInterface;
use Temando\Shipping\Rest\Adapter\ShipmentApiInterface;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Exception\RestClientErrorException;
use Temando\Shipping\Rest\Request\ItemRequestInterface;
use Temando\Shipping\Rest\Request\RequestHeadersInterface;
use Temando\Shipping\Rest\Response\DataObject\Batch;
use Temando\Shipping\Rest\Response\DataObject\Shipment;
use Temando\Shipping\Rest\Response\Document\Errors;
use Temando\Shipping\Rest\Response\Document\GetBatch;
use Temando\Shipping\Rest\Response\Document\GetShipment;
use Temando\Shipping\Rest\SchemaMapper\ParserInterface;
use Temando\Shipping\Webservice\Config\WsConfigInterface;

/**
 * Temando REST API Shipment Operations Adapter
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShipmentAdapter implements BatchApiInterface, ShipmentApiInterface
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $accountId;

    /**
     * @var string
     */
    private $bearerToken;

    /**
     * @var RequestHeadersInterface
     */
    private $requestHeaders;

    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * @var RestClientInterface
     */
    private $restClient;

    /**
     * @var ParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ShipmentAdapter constructor.
     * @param WsConfigInterface $config
     * @param RequestHeadersInterface $requestHeaders
     * @param AuthenticationInterface $auth,
     * @param RestClientInterface $restClient
     * @param ParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        WsConfigInterface $config,
        RequestHeadersInterface $requestHeaders,
        AuthenticationInterface $auth,
        RestClientInterface $restClient,
        ParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->endpoint = $config->getApiEndpoint();
        $this->accountId = $config->getAccountId();
        $this->bearerToken = $config->getBearerToken();

        $this->requestHeaders = $requestHeaders;
        $this->auth = $auth;
        $this->restClient = $restClient;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * Read a batch from the platform.
     *
     * @param ItemRequestInterface $request
     * @return Batch
     * @throws AdapterException
     */
    public function getBatch(ItemRequestInterface $request)
    {
        $uri = sprintf('%s/shipments/batches/%s', $this->endpoint, ...$request->getPathParams());

        $this->logger->log(LogLevel::DEBUG, $uri);

        try {
            $this->auth->connect($this->accountId, $this->bearerToken);
            $headers = $this->requestHeaders->getHeaders();

            $rawResponse = $this->restClient->get($uri, [], $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var GetBatch $response */
            $response = $this->responseParser->parse($rawResponse, GetBatch::class);
            $batch = $response->getData();
            $batch->setShipments($response->getIncluded());
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $batch;
    }

    /**
     * Read one shipment from the platform.
     *
     * @param ItemRequestInterface $request
     * @return Shipment
     * @throws AdapterException
     */
    public function getShipment(ItemRequestInterface $request)
    {
        $uri = sprintf('%s/shipments/%s', $this->endpoint, ...$request->getPathParams());

        $this->logger->log(LogLevel::DEBUG, $uri);

        try {
            $this->auth->connect($this->accountId, $this->bearerToken);
            $headers = $this->requestHeaders->getHeaders();

            $rawResponse = $this->restClient->get($uri, [], $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var GetShipment $response */
            $response = $this->responseParser->parse($rawResponse, GetShipment::class);
            $shipment = $response->getData();
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $shipment;
    }

    /**
     * Cancel shipment at the platform.
     *
     * @param ItemRequestInterface $request
     * @return Shipment
     * @throws AdapterException
     */
    public function cancelShipment(ItemRequestInterface $request)
    {
        $uri = sprintf('%s/shipments/%s/cancel', $this->endpoint, ...$request->getPathParams());

        $this->logger->log(LogLevel::DEBUG, $uri);

        try {
            $this->auth->connect($this->accountId, $this->bearerToken);
            $headers = $this->requestHeaders->getHeaders();

            $rawResponse = $this->restClient->get($uri, [], $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var GetShipment $response */
            $response = $this->responseParser->parse($rawResponse, GetShipment::class);
            $shipment = $response->getData();
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $shipment;
    }
}
