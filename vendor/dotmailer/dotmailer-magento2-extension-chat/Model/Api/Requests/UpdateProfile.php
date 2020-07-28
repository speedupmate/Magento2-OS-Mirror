<?php

namespace Dotdigitalgroup\Chat\Model\Api\Requests;

use Dotdigitalgroup\Chat\Model\Api\LiveChatApiClient;
use Dotdigitalgroup\Chat\Model\Api\LiveChatRequestInterface;
use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Email\Logger\Logger;
use Zend\Http\Request;

class UpdateProfile implements LiveChatRequestInterface
{
    /**
     * @var LiveChatApiClient
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * UpdateProfile constructor
     *
     * @param LiveChatApiClient $client
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        LiveChatApiClient $client,
        Config $config,
        Logger $logger
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param string $profileId
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function send(string $profileId, array $data = [])
    {
        try {
            return $this->client->request(
                sprintf('apispaces/%s/profiles/%s', $this->config->getApiSpaceId(), $profileId),
                Request::METHOD_PATCH,
                $data
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
