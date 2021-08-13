<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Contains options for summarize tax
 */
class SummarizeTax implements OptionSourceInterface
{
    const PRODUCT_AND_SHIPPING = 'product_and_shipping';
    const JURISDICTION = 'jurisdiction';

    /**
     * Available options for SummarizeTax
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Product and Shipping'),
                'value' => static::PRODUCT_AND_SHIPPING
            ],

            [
                'label' => __('Jurisdiction'),
                'value' => static::JURISDICTION
            ]
        ];
    }
}
