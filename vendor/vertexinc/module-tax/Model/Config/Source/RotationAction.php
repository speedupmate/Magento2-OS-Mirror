<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Represents a type of log rotation in use
 */
class RotationAction implements OptionSourceInterface
{
    const TYPE_DELETE = 'delete';
    const TYPE_EXPORT = 'export';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
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
