<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField\Processor;

use Magento\Framework\Phrase;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;

/**
 * Updates the label on flexible field attribute objects
 */
class AttributeRenamer
{
    /**
     * Update the label on attributes
     *
     * Useful for setting human friendly names on attributes that are automatically generated from Interface methods
     *
     * @param string[]|Phrase[] $newNames Attribute Code => New Name
     * @param FlexFieldProcessableAttribute[] $attributes
     * @return FlexFieldProcessableAttribute[]
     */
    public function execute(array $attributes, array $newNames): array
    {
        foreach ($newNames as $attributeCode => $newLabel) {
            if (!isset($attributes[$attributeCode])) {
                continue;
            }

            $newLabel = $newLabel instanceof Phrase ? $newLabel->render() : $newLabel;

            $attributes[$attributeCode]->setLabel($newLabel);
        }

        return $attributes;
    }
}
