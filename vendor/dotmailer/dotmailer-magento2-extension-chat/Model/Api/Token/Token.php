<?php

namespace Dotdigitalgroup\Chat\Model\Api\Token;

use Dotdigitalgroup\Chat\Model\Config;
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
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     * @param JwtDecoder $jwtDecoder
     * @param DateTimeFactory $dateTimeFactory
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        JwtDecoder $jwtDecoder,
        DateTimeFactory $dateTimeFactory,
        Logger $logger
    ) {
        $this->config = $config;
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
        $storedToken = $this->config->getApiToken();

        try {
            $jwt = $this->encryptor->decrypt($storedToken);
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
        $client = $this->config->getApiClient();
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
