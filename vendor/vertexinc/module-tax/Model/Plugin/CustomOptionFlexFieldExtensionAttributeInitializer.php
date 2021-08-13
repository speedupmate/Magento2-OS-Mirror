<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

/**
 * Performs initialization of the vertex_flex_field extension attribute on Product Custom Options on Admin panel save
 *
 * @see Helper Intercepted class
 */
class CustomOptionFlexFieldExtensionAttributeInitializer
{
    /**
     * Convert the vertex_flex_field from a normal attribute to an extension attribute
     *
     * The helper method  we're intercepting is responsible for setting up the product options based off the form
     * submitted during product save - so that when they're loaded into the productOption objects they're in a format
     * expected to properly save to the database.
     *
     * @param Helper $subject
     * @param array $productOptions
     * @return array
     * @see Helper::mergeProductOptions() Intercepted method
     */
    public function afterMergeProductOptions(Helper $subject, array $productOptions): array
    {
        foreach ($productOptions as $optionIndex => $option) {
            if (isset($option['vertex_flex_field']) && $option['vertex_flex_field']) {
                $productOptions[$optionIndex]['extension_attributes']
                ['data']['vertex_flex_field'] = $option['vertex_flex_field'];
            }
        }
        return $productOptions;
    }
}
