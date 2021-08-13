<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vertex\RequestLoggingApi\Model\RetrieveLogLevelInterface;

class LogLevel implements OptionSourceInterface
{
    /** @var array */
    private $options;

    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = $this->getLogLevels();
        }

        return $this->options;
    }

    private function getLogLevels(): array
    {
        return [
            '0' => [
                'label' => 'None',
                'value' => RetrieveLogLevelInterface::LEVEL_NONE
            ],
            '1' => [
                'label' => 'All Requests',
                'value' => RetrieveLogLevelInterface::LEVEL_TRACE
            ],
            '2' => [
                'label' => 'Errors',
                'value' => RetrieveLogLevelInterface::LEVEL_ERROR
            ]
        ];
    }
}
