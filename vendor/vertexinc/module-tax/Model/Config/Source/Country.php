<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vertex\Tax\Model\ResourceModel\Country\Collection;

/**
 * Frontend source model for country select drop down
 */
class Country implements OptionSourceInterface
{
    /** @var Collection */
    private $countryCollection;

    /** @var array */
    private $options;

    /**
     * @param Collection $countryCollection
     */
    public function __construct(Collection $countryCollection)
    {
        $this->countryCollection = $countryCollection;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->countryCollection->toOptionArrayISO3();
            array_unshift($this->options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $this->options;
    }
}
