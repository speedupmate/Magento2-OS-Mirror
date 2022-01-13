<?php

namespace Dotdigitalgroup\Chat\Model\Api;

use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Chat\Model\Api\Token\Token;
use Zend\Http\Client as HttpClient;
use Zend\Http\ClientFactory;
use Zend\Http\Response;

class LiveChatApiClient
{
    /**
     * Chat API hostname
     */
    const CHAT_API_HOST = 'https://api.comapi.com';

    /**
     * Chat config
     *
     * @var Config
     */
    private $config;

    /**
     * Zend HTTP Client
     *
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Token
     */
    private $token;

    /**
     * Client constructor
     *
     * @param Config $config
     * @param ClientFactory $clientFactory
     * @param Token $token
     */
    public function __construct(
        Config $config,
        ClientFactory $clientFactory,
        Token $token
    ) {
        $this->config = $config;
        $this->httpClientFactory = $clientFactory;
        $this->token = $token;
    }

    /**
     * Send a request to the Chat API
     *
     * @param string $endpoint
     * @param string $method
     * @param array $body
     * @param string $apiToken
     * @return Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function request($endpoint, $method, array $body = [], $apiToken = null)
    {
        // set up client
        $apiToken = $apiToken ?: $this->token->getApiToken();

        /** @var HttpClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setMethod($method)
            ->setUri(sprintf('%s/%s', self::CHAT_API_HOST, $endpoint))
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiToken,
            ]);

        // add JSON body, if required
        if (!empty($body)) {
            $httpClient->setRawBody(json_encode($body));
        }
        return $httpClient->send();
    }
}
