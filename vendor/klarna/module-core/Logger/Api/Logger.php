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

use Klarna\Core\Api\ServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Logger
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var Update
     */
    private $loggerUpdate;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $config
     * @param Update                $loggerUpdate
     * @codeCoverageIgnore
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        Update $loggerUpdate
    ) {
        $this->storeManager = $storeManager;
        $this->config       = $config;
        $this->loggerUpdate = $loggerUpdate;
    }

    /**
     * Logging the content of the container to the database
     *
     * @param Container $loggerContainer
     * @throws LocalizedException
     */
    public function logContainer(Container $loggerContainer): void
    {
        if (!$this->config->isSetFlag(
            'klarna/api/request_logging',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        )) {
            return;
        }

        $this->loggerUpdate->addEntry($loggerContainer);
    }
}
