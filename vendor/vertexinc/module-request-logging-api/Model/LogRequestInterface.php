<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLoggingApi\Model;

use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;

/**
 * Request Logger
 *
 * @api
 */
interface LogRequestInterface
{
    /**
     * Log a request
     */
    public function execute(
        LogEntryInterface $logEntry,
        #[ExpectedValues(valuesFromClass: RetrieveLogLevelInterface::class)]
        int $logLevel = RetrieveLogLevelInterface::LEVEL_TRACE
    ): bool;
}
