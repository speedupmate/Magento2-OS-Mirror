<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Represents a type of log rotation in use
 */
class RotationAction implements OptionSourceInterface
{
    const TYPE_DELETE = 'delete';
    const TYPE_EXPORT = 'export';

    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Export to file and delete'),
                'value' => static::TYPE_EXPORT
            ],
            [
                'label' => __('Delete'),
                'value' => static::TYPE_DELETE
            ]
        ];
    }
}
