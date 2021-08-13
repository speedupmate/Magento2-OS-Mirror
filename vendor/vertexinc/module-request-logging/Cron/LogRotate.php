<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Cron;

use Magento\Framework\Exception\CouldNotDeleteException;
use Vertex\RequestLogging\Model\Config;
use Vertex\RequestLogging\Model\LogEntryRotator;
use Vertex\RequestLogging\Model\LogEntryRotatorFactory;

/**
 * Class triggered by cron to rotate the vertex_taxrequest table
 */
class LogRotate
{
    /** @var Config */
    private $config;

    /** @var LogEntryRotatorFactory */
    private $logEntryRotatorFactory;

    /**
     * @param LogEntryRotatorFactory $logEntryRotatorFactory
     * @param Config $config
     */
    public function __construct(
        LogEntryRotatorFactory $logEntryRotatorFactory,
        Config $config
    ) {
        $this->logEntryRotatorFactory = $logEntryRotatorFactory;
        $this->config = $config;
    }

    /**
     * Rotate expired entries in the log entry table.
     *
     * @throws CouldNotDeleteException
     */
    public function execute(): void
    {
        if ($this->config->isLogRotationEnabled()) {
            $lifetimeSeconds = 3600 * 24 * (int)$this->config->getCronLogLifetime();

            try {
                /** @var LogEntryRotator $rotator */
                $rotator = $this->logEntryRotatorFactory->create();
                $rotator->rotate($lifetimeSeconds);
            } catch (\Exception $e) {
                throw new CouldNotDeleteException(__('Could not successfully delete record(s)'), $e);
            }
        }
    }
}
