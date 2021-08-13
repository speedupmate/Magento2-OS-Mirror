<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;

/**
 * Extract return type of a custom attribute
 */
class EavTypeExtractor
{
    /**
     * Retrieve the return type of a method
     *
     * @param Attribute $attribute
     * @return string
     */
    public function extract($attribute)
    {
        if (in_array($attribute->getBackendType(), ['int', 'decimal', 'integer'])
            && (!$attribute->usesSource() || $attribute->getSourceModel() instanceof Boolean)
        ) {
            return FlexibleFieldSource::TYPE_NUMERIC;
        }

        if ($attribute->getBackendType() === 'datetime') {
            return FlexibleFieldSource::TYPE_DATE;
        }

        return FlexibleFieldSource::TYPE_CODE;
    }
}
