<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Vertex\Tax\Model\Config\Source\Country;

/**
 * HTML select for countries
 */
class Countries extends Select
{
    /** @var Country */
    private $country;

    /**
     * @param Context $context
     * @param Country $country
     * @param array $data
     */
    public function __construct(Context $context, Country $country, array $data = [])
    {
        parent::__construct($context, $data);
        $this->country = $country;
    }

    /**
     * Get country options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->country->toOptionArray();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }
}
