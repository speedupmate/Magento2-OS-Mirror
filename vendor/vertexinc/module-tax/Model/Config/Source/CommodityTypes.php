<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Commodity Types source model
 */
class CommodityTypes implements OptionSourceInterface
{
    /** @var array */
    private $options;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->getCommodityTypes();
            array_unshift($this->options, ['value' => null, 'label' => __('Please Select.')]);
        }

        return $this->options;
    }

    /**
     * Return commodity types
     *
     * @return array
     */
    public function getCommodityTypes()
    {
        return [
            'unspsc' => [
                'label' => 'UNSPSC',
                'value' => 'UNSPSC'
            ],
            'ncm' => [
                'label' => 'NCM',
                'value' => 'NCM'
            ],
            'service' => [
                'label' => 'Service',
                'value' => 'Service'
            ],
            'hsn' => [
                'label' => 'HSN',
                'value' => 'HSN'
            ]
        ];
    }
}
