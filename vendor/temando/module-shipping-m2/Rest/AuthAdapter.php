<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Temando\Shipping\Rest\Adapter\AuthenticationApiInterface;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Exception\RestClientErrorException;
use Temando\Shipping\Rest\Request\AuthRequest;
use Temando\Shipping\Rest\Response\DataObject\Session;
use Temando\Shipping\Rest\Response\Document\Errors;
use Temando\Shipping\Rest\Response\Document\GetSession;
use Temando\Shipping\Rest\SchemaMapper\ParserInterface;
use Temando\Shipping\Webservice\Config\WsConfigInterface;

/**
 * Temando REST API Authentication Adapter
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AuthAdapter implements AuthenticationApiInterface
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $apiVersion;

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
     * AuthAdapter constructor.
     * @param WsConfigInterface $config
     * @param RestClientInterface $restClient
     * @param ParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        WsConfigInterface $config,
        RestClientInterface $restClient,
        ParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->endpoint = $config->getSessionEndpoint();
        $this->apiVersion = $config->getApiVersion();

        $this->restClient = $restClient;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * @param AuthRequest $request
     * @return Session
     * @throws AdapterException
     */
    public function startSession(AuthRequest $request)
    {
        $uri = sprintf('%s/sessions', $this->endpoint);
        $requestBody = $request->getRequestBody();

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'application/vnd.api+json',
            'Accept'        => 'application/vnd.api+json',
            'Version'       => $this->apiVersion,
        ];

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));

        try {
            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var GetSession $response */
            $response = $this->responseParser->parse($rawResponse, GetSession::class);
            $session = $response->getData();
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $session;
    }

    /**
     * @return bool
     */
    public function endSession()
    {
        return false;
    }
}
