<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Logger\Api;

use Klarna\Core\Logger\Cleanser;
use Klarna\Core\Model\LogFactory;
use Klarna\Core\Model\LogRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Update
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var LogFactory
     */
    private $logFactory;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Cleanser
     */
    private $cleanser;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ScopeConfigInterface  $config
     * @param StoreManagerInterface $storeManager
     * @param LogFactory            $logFactory
     * @param LogRepository         $logRepository
     * @param Json                  $json
     * @param Cleanser              $cleanser
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        LogFactory $logFactory,
        LogRepository $logRepository,
        Json $json,
        Cleanser $cleanser,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->config                = $config;
        $this->storeManager          = $storeManager;
        $this->logFactory            = $logFactory;
        $this->logRepository         = $logRepository;
        $this->json                  = $json;
        $this->cleanser              = $cleanser;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Adding an entry in the database
     *
     * @param Container $loggerContainer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addEntry(Container $loggerContainer): void
    {
        $request  = $loggerContainer->getRequest();
        $response = $loggerContainer->getResponse();

        if (!$this->config->isSetFlag(
            'klarna/api/test_mode',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        )) {
            $request  = $this->anonymizeData($request);
            $response = $this->anonymizeData($response);
        }

        $log = $this->logFactory->create();
        $log->setStatus($response['response_status_code']);
        $log->setAction($loggerContainer->getAction());
        $log->setKlarnaId($loggerContainer->getKlarnaId());
        $log->setIncrementId($loggerContainer->getIncrementId());
        $log->setUrl($loggerContainer->getUrl());
        $log->setMethod($loggerContainer->getMethod());
        $log->setService($loggerContainer->getService());
        $log->setRequest($this->json->serialize($request));
        $log->setResponse($this->json->serialize($response));

        $this->logRepository->save($log);
    }

    /**
     * Anonymize data
     *
     * @param array $data
     * @return array
     */
    private function anonymizeData(array $data): array
    {
        $keys = [
            'billing_address',
            'shipping_address'
        ];
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data[$key] = $this->cleanser->checkForSensitiveData($data[$key]);
            }
        }

        return $data;
    }
}
