<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLoggingApi\Model;

/**
 * Log Level Configuration Retriever
 *
 * @api
 */
interface RetrieveLogLevelInterface
{
    public const LEVEL_TRACE = 1;
    public const LEVEL_NONE = 0;
    public const LEVEL_ERROR = 100;

    /**
     * Retrieve the configured log level
     */
    public function execute(): int;
}
