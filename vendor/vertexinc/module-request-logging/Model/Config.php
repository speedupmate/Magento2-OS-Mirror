<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration retrieval tool
 */
class Config
{
    public const CONFIG_XML_PATH_LOG_LEVEL = 'tax/vertex_logging/enable_logging';
    public const CONFIG_XML_PATH_ROTATION_ACTION = 'tax/vertex_logging/rotation_action';
    public const CONFIG_XML_PATH_VERTEX_ENABLE_LOG_ROTATION = 'tax/vertex_logging/enable_rotation';
    public const CONFIG_XML_PATH_VERTEX_LOG_ROTATION_FREQUENCY = 'tax/vertex_logging/rotation_frequency';
    public const CONFIG_XML_PATH_VERTEX_LOG_ROTATION_RUNTIME = 'tax/vertex_logging/rotation_runtime';
    public const VERTEX_LOG_AMOUNT_PER_BATCH = 'tax/vertex_logging/amount_per_batch';
    public const VERTEX_LOG_LIFETIME_DAYS = 'tax/vertex_logging/entry_lifetime';
    public const CRON_STRING_PATH = 'crontab/vertex_log/jobs/vertex_log_rotation/schedule/cron_expr';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve a value from the configuration within a scope
     */
    public function getConfigValue(
        string $value,
        string $scopeId = null,
        string $scope = ScopeInterface::SCOPE_STORE
    ): ?string {
        return $this->scopeConfig->getValue($value, $scope, $scopeId);
    }

    /**
     * Retrieve the amount of logs per batch to be processed at once
     */
    public function getCronAmountPerBatch(): int
    {
        return (int)$this->getConfigValue(self::VERTEX_LOG_AMOUNT_PER_BATCH);
    }

    /**
     * Retrieve the lifetime of logs, in days, before they are rotated
     */
    public function getCronLogLifetime(): ?string
    {
        return $this->getConfigValue(self::VERTEX_LOG_LIFETIME_DAYS);
    }

    /**
     * Retrieve the frequency at which the cron should run
     */
    public function getCronRotationFrequency(): ?string
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_LOG_ROTATION_FREQUENCY);
    }

    /**
     * Retrieve the time of day logs should be rotated
     */
    public function getCronRotationTime(): ?string
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_VERTEX_LOG_ROTATION_RUNTIME);
    }

    /**
     * Retrieve the most verbose level of logging desired
     */
    public function getLogLevel(): int
    {
        return (int)$this->getConfigValue(self::CONFIG_XML_PATH_LOG_LEVEL);
    }

    /**
     * Retrieve the type of action to take to logs when rotating
     */
    public function getRotationAction(): ?string
    {
        return $this->getConfigValue(self::CONFIG_XML_PATH_ROTATION_ACTION);
    }

    /**
     * Determine if Vertex Archiving has been enabled.
     */
    public function isLogRotationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_XML_PATH_VERTEX_ENABLE_LOG_ROTATION);
    }
}
