<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model;

use Vertex\RequestLoggingApi\Model\RetrieveLogLevelInterface;

class RetrieveLogLevel implements RetrieveLogLevelInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function execute(): int
    {
        return $this->config->getLogLevel();
    }
}
