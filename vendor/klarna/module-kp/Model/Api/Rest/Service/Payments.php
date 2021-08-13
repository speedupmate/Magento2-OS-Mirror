<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Model\Api\Rest\Service;

use Klarna\Core\Api\ServiceInterface;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\KlarnaConfig;
use Klarna\Core\Helper\VersionInfo;
use Klarna\Core\Model\Api\Exception as KlarnaApiException;
use Klarna\Core\Logger\Api\Container;
use Klarna\Kp\Api\CreditApiInterface;
use Klarna\Kp\Api\Data\RequestInterface;
use Klarna\Kp\Api\Data\ResponseInterface;
use Klarna\Kp\Model\Api\Response;
use Klarna\Kp\Model\Api\ResponseFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payments implements CreditApiInterface
{
    const API_VERSION = 'v1';

    /**
     * @var ServiceInterface
     */
    private $service;
    /**
     * @var VersionInfo
     */
    private $versionInfo;
    /**
     * @var LoggerInterface $log
     */
    private $log;
    /**
     * @var StoreInterface
     */
    private $store;
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var KlarnaConfig
     */
    private $klarnaConfig;
    /**
     * @var Container
     */
    private $loggerContainer;

    /**
     * @param ScopeConfigInterface  $config
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface       $log
     * @param VersionInfo           $versionInfo
     * @param ResponseFactory       $responseFactory
     * @param ConfigHelper          $configHelper
     * @param KlarnaConfig          $klarnaConfig
     * @param ServiceInterface      $service
     * @param Container|null        $loggerContainer
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        LoggerInterface $log,
        VersionInfo $versionInfo,
        ResponseFactory $responseFactory,
        ConfigHelper $configHelper,
        KlarnaConfig $klarnaConfig,
        ServiceInterface $service,
        Container $loggerContainer = null
    ) {
        $this->log = $log;
        $this->service = $service;
        $this->responseFactory = $responseFactory;
        $this->store = $storeManager->getStore();
        $this->config = $config;
        $this->versionInfo = $versionInfo;
        $this->configHelper = $configHelper;
        $this->klarnaConfig = $klarnaConfig;
        $this->loggerContainer = $loggerContainer ?? ObjectManager::getInstance()->get(Container::class);
    }

    /**
     * @param string                $url
     * @param string                $action
     * @param null|RequestInterface $request
     * @param string                $method
     * @param null|string           $klarnaId
     * @return Response
     * @throws \Klarna\Core\Exception
     */
    private function processRequest(
        string $url,
        string $action,
        RequestInterface $request = null,
        string $method = ServiceInterface::POST,
        string $klarnaId = null
    ) {
        $this->loggerContainer->setAction($action);
        $body = $this->getBody($request);
        $this->connect();
        $response = $this->service->makeRequest($url, $body, $method, $klarnaId);
        $response['response_code'] = $response['response_status_code'];
        return $this->responseFactory->create(['data' => $response]);
    }

    /**
     * Getting back the body
     *
     * @param RequestInterface|null $request
     * @return array
     */
    private function getBody(RequestInterface  $request = null): array
    {
        if ($request) {
            return $request->toArray();
        }

        return [];
    }

    /**
     * @throws \Klarna\Core\Exception
     */
    private function connect()
    {
        $version = sprintf(
            '%s;Core/%s;OM/%s',
            $this->versionInfo->getVersion('Klarna_Kp'),
            $this->versionInfo->getVersion('Klarna_Core'),
            $this->versionInfo->getVersion('Klarna_Ordermanagement')
        );
        $mageMode = $this->versionInfo->getMageMode();
        $mageVersion = $this->versionInfo->getMageEdition() . '/' . $this->versionInfo->getMageVersion();
        $mageInfo = "Magento {$mageVersion} {$mageMode} mode";
        $this->service->setUserAgent('Magento2_KP', $version, $mageInfo);
        $this->service->setHeader('Accept', '*/*');

        $username = $this->config->getValue('klarna/api/merchant_id', ScopeInterface::SCOPE_STORES, $this->store);
        $password = $this->config->getValue('klarna/api/shared_secret', ScopeInterface::SCOPE_STORES, $this->store);
        $test_mode = $this->config->getValue('klarna/api/test_mode', ScopeInterface::SCOPE_STORES, $this->store);

        $versionConfig = $this->klarnaConfig->getVersionConfig($this->store);
        $url = $versionConfig->getUrl($test_mode);

        $this->service->connect($username, $password, $url);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws KlarnaApiException
     * @throws \Klarna\Core\Exception
     */
    public function createSession(RequestInterface $request)
    {
        return $this->processRequest(
            '/payments/' . self::API_VERSION . '/sessions',
            ServiceInterface::ACTIONS['create_session'],
            $request,
            ServiceInterface::POST,
            null
        );
    }

    /**
     * @param string           $sessionId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws KlarnaApiException
     * @throws \Klarna\Core\Exception
     */
    public function updateSession($sessionId, RequestInterface $request)
    {
        $response = $this->processRequest(
            '/payments/' . self::API_VERSION . '/sessions/' . $sessionId,
            ServiceInterface::ACTIONS['update_session'],
            $request,
            ServiceInterface::POST,
            $sessionId
        );
        if ($response->getResponseCode() === 204) {
            return $this->readSession($sessionId);
        }
        return $response;
    }

    /**
     * @param string           $sessionId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws KlarnaApiException
     * @throws \Klarna\Core\Exception
     */
    public function readSession($sessionId)
    {
        $resp = $this->processRequest(
            '/payments/' . self::API_VERSION . '/sessions/' . $sessionId,
            ServiceInterface::ACTIONS['read_session'],
            null,
            ServiceInterface::GET,
            $sessionId
        );
        $response = $resp->toArray();
        $response['session_id'] = $sessionId;
        return $this->responseFactory->create(['data' => $response]);
    }

    /**
     * @param string           $authorization_token
     * @param RequestInterface $request
     * @param null|string      $klarnaId
     * @param null|string      $incrementId
     * @return ResponseInterface
     * @throws KlarnaApiException
     * @throws \Klarna\Core\Exception
     */
    public function placeOrder(
        $authorization_token,
        RequestInterface $request,
        $klarnaId = null,
        string $incrementId = null
    ) {
        $this->loggerContainer->setIncrementId($incrementId);

        return $this->processRequest(
            '/payments/' . self::API_VERSION . '/authorizations/' . $authorization_token . '/order',
            ServiceInterface::ACTIONS['create_order'],
            $request,
            ServiceInterface::POST,
            $klarnaId
        );
    }

    /**
     * @param string $authorization_token
     * @param null   $klarnaId
     * @return ResponseInterface
     * @throws KlarnaApiException
     * @throws \Klarna\Core\Exception
     */
    public function cancelOrder($authorization_token, $klarnaId = null)
    {
        return $this->processRequest(
            '/payments/' . self::API_VERSION . '/authorizations/' . $authorization_token,
            ServiceInterface::ACTIONS['cancel_order'],
            null,
            ServiceInterface::DELETE,
            $klarnaId
        );
    }
}
