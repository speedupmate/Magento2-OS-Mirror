<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Temando\Shipping\Rest\Adapter\ExperienceApiInterface;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Exception\RestClientErrorException;
use Temando\Shipping\Rest\Request\ListRequestInterface;
use Temando\Shipping\Rest\Request\QualifyRequest;
use Temando\Shipping\Rest\Request\RequestHeadersInterface;
use Temando\Shipping\Rest\Response\DataObject\Experience;
use Temando\Shipping\Rest\Response\DataObject\OrderQualification;
use Temando\Shipping\Rest\Response\Document\Errors;
use Temando\Shipping\Rest\Response\Document\GetExperiences;
use Temando\Shipping\Rest\Response\Document\QualifyOrder;
use Temando\Shipping\Rest\SchemaMapper\ParserInterface;
use Temando\Shipping\Webservice\Config\WsConfigInterface;

/**
 * Temando REST API Experience Operations Adapter
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ExperienceAdapter implements ExperienceApiInterface
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
     * ExperienceAdapter constructor.
     * @param WsConfigInterface $config
     * @param RequestHeadersInterface $requestHeaders
     * @param AuthenticationInterface $auth
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
     * Retrieve shipping options for the current quote.
     *
     * @param QualifyRequest $request
     * @return OrderQualification[]
     * @throws AdapterException
     */
    public function qualify(QualifyRequest $request)
    {
        $uri = sprintf('%s/qualify', $this->endpoint);
        $requestBody = $request->getRequestBody();

        $this->logger->log(LogLevel::DEBUG, sprintf("%s\n%s", $uri, $requestBody));

        try {
            $this->auth->connect($this->accountId, $this->bearerToken);
            $headers = $this->requestHeaders->getHeaders();

            $rawResponse = $this->restClient->post($uri, $requestBody, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var QualifyOrder $response */
            $response = $this->responseParser->parse($rawResponse, QualifyOrder::class);
            $qualifications = $response->getData();
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            throw AdapterException::create($e);
        }

        return $qualifications;
    }

    /**
     * Obtain shipping experiences.
     *
     * @param ListRequestInterface $request
     * @return Experience[]
     * @throws AdapterException
     */
    public function getExperiences(ListRequestInterface $request)
    {
        $uri = sprintf('%s/experiences', $this->endpoint);
        $queryParams = $request->getRequestParams();

        $this->logger->log(LogLevel::DEBUG, sprintf('%s?%s', $uri, http_build_query($queryParams)));

        try {
            $this->auth->connect($this->accountId, $this->bearerToken);
            $headers = $this->requestHeaders->getHeaders();

            $rawResponse = $this->restClient->get($uri, $queryParams, $headers);
            $this->logger->log(LogLevel::DEBUG, $rawResponse);

            /** @var GetExperiences $response */
            $response = $this->responseParser->parse($rawResponse, GetExperiences::class);
            $experiences = $response->getData();
        } catch (RestClientErrorException $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            /** @var Errors $response */
            $response = $this->responseParser->parse($e->getMessage(), Errors::class);
            throw AdapterException::errorResponse($response, $e);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $experiences = [];
        }

        return $experiences;
    }
}
