<?php

namespace Dotdigitalgroup\Chat\Model\Api\Token;

use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Email\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;
use Dotdigitalgroup\Email\Logger\Logger;

class Token
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JwtDecoder
     */
    private $jwtDecoder;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $websiteId = 0;

    /**
     * We want to allow a small amount of time when checking the token expiry,
     * to account for 'clock skew' or just the time the script takes to proceed
     * from checking the token to actually making the API call.
     *
     * @var int
     */
    private $leeway = 60;

    /**
     * Token constructor
     *
     * @param Config $config
     * @param Data $helper
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     * @param JwtDecoder $jwtDecoder
     * @param DateTimeFactory $dateTimeFactory
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        Data $helper,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        JwtDecoder $jwtDecoder,
        DateTimeFactory $dateTimeFactory,
        Logger $logger
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->jwtDecoder = $jwtDecoder;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getApiToken()
    {
        $value = $this->scopeConfig->getValue(
            Config::XML_PATH_LIVECHAT_API_TOKEN,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->websiteId
        );

        try {
            $jwt = $this->encryptor->decrypt($value);
            $jwtPayload = $this->jwtDecoder->decode($jwt);
        } catch (\InvalidArgumentException $e) {
            return $this->refreshToken();
        }

        $tokenExpiryTimestamp = $jwtPayload['exp'] ?? 0;

        if ($this->isNotExpired($tokenExpiryTimestamp)) {
            return $jwt;
        }

        return $this->refreshToken();
    }

    /**
     * Checks if token is not expired.
     *
     * @param int $expTimestamp
     * @return bool
     */
    private function isNotExpired(int $expTimestamp)
    {
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        return ($currentDate->getTimestamp() + $this->leeway) < $expTimestamp;
    }

    /**
     * If our stored token is expired or has no expiry,
     * re-route back to EC to retrieve a new token.
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function refreshToken()
    {
        $client = $this->helper->getWebsiteApiClient($this->websiteId);
        $response = $client->setUpChatAccount();

        if (!$response || isset($response->message)) {
            throw new LocalizedException(
                __("Error refreshing chat API token. Message: " . ($response->message ?? 'No message'))
            );
        }

        $this->logger->info('Chat API token refreshed');

        $this->config->saveChatApiToken($response->token)
            ->reinitialiseConfig();

        return $response->token;
    }
}
