<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Model\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Core\Logger\Api\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Klarna\Core\Logger\Api\Logger;

class Service implements ServiceInterface
{
    /**
     * Holds headers to be sent in HTTP request
     *
     * @var array
     */
    private $headers = [];
    /**
     * The base URL to interact with
     *
     * @var string
     */
    private $uri = '';
    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var LoggerInterface $log
     */
    private $log;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Logger
     */
    private $apiLogger;
    /**
     * @var Container
     */
    private $loggerContainer;

    /**
     * @param LoggerInterface $log
     * @param Logger|null     $apiLogger
     * @param Container|null  $loggerContainer
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $log,
        Logger $apiLogger = null,
        Container $loggerContainer = null
    ) {
        $this->log             = $log;
        $this->apiLogger       = $apiLogger ?? ObjectManager::getInstance()->get(Logger::class);
        $this->loggerContainer = $loggerContainer ?? ObjectManager::getInstance()->get(Container::class);

        // Client cannot be injected in constructor because Magento Object Manager in 2.1 has problems with it
        $this->client = new Client();
    }

    /**
     * @inheritdoc
     */
    public function setUserAgent($product, $version, $mageInfo)
    {
        $baseUA = sprintf('Guzzle/%s;PHP/%s', \GuzzleHttp\Client::VERSION, PHP_VERSION);
        $this->setHeader(
            'User-Agent',
            sprintf('%s/%s;%s (%s)', $product, $version, $baseUA, $mageInfo)
        );
    }

    /**
     * @inheritdoc
     */
    public function setHeader($header, $value = null)
    {
        if (!$value) {
            unset($this->headers[$header]);
            return;
        }
        $this->headers[$header] = $value;
    }

    /**
     * @inheritdoc
     */
    public function makeRequest(
        $url,
        $body = [],
        $method = ServiceInterface::POST,
        string $klarnaId = null
    ) {
        $response = [
            'is_successful' => false
        ];
        try {
            $data = [
                'headers' => $this->headers,
                'json'    => $body
            ];
            $data = $this->getAuth($data);
            unset($data['increment_id']);

            $this->loggerContainer->setKlarnaId($klarnaId);
            $this->loggerContainer->setUrl($url);
            $this->loggerContainer->setRequest($body);
            $this->loggerContainer->setMethod($method);
            $this->loggerContainer->setService(ServiceInterface::SERVICE);

            /** @var ResponseInterface $response */
            $response = $this->client->$method($this->uri . $url, $data);
            $response = $this->processResponse($response);
            $response['is_successful'] = true;

            $klarnaId = $klarnaId ?? $response['session_id'];
            $this->loggerContainer->setKlarnaId($klarnaId);
        } catch (BadResponseException $e) {
            $response['response_status_code'] = $e->getCode();
            $response['response_status_message'] = $e->getMessage();
            $response = $this->processResponse($response);
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                try {
                    $body = $this->processResponse($errorResponse);
                } catch (\Exception $e) {
                    $response['exception_code'] = $e->getCode();
                }
                $response = array_merge($response, $body);
            }
            $response['exception_code'] = $e->getCode();
        } catch (\Exception $e) {
            $this->log->error('Exception: ' . $e->getMessage());
            $response['exception_code'] = $e->getCode();
        }

        $this->loggerContainer->setResponse($response);
        $this->apiLogger->logContainer($this->loggerContainer);

        return $response;
    }

    /**
     * Set auth data if username or password has been provided
     *
     * @param $data
     * @return mixed
     */
    private function getAuth($data)
    {
        if ($this->username || $this->password) {
            $data['auth'] = [$this->username, $this->password];
        }
        return $data;
    }

    /**
     * Process the response and return an array
     *
     * @param ResponseInterface|array $response
     * @return array
     * @throws \Klarna\Core\Model\Api\Exception
     */
    private function processResponse($response)
    {
        if (is_array($response)) {
            return $response;
        }
        try {
            $data = json_decode((string)$response->getBody(), true);
        } catch (\Exception $e) {
            $data = [
                'exception' => $e->getMessage()
            ];
        }
        if ($response->getStatusCode() === 401) {
            throw new KlarnaApiException(__($response->getReasonPhrase()));
        }
        $data['response_object'] = [
            'headers' => $response->getHeaders(),
            'body'    => $response->getBody()->getContents()
        ];
        $data['response_status_code'] = $response->getStatusCode();
        $data['response_status_message'] = $response->getReasonPhrase();
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function connect($username, $password, $connectUrl = null)
    {
        $this->username = $username;
        $this->password = $password;
        if ($connectUrl) {
            $this->uri = $connectUrl;
        }
        return true;
    }
}
