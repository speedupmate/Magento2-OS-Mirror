<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field\FieldSource;

/**
 * Convert Flex Field Source options into flex-field-select options
 */
interface OptionProviderInterface
{
    /**
     * Convert Flex Field Source options into flex-field-select options
     *
     * @return array
     */
    public function getOptions(): array;
}
