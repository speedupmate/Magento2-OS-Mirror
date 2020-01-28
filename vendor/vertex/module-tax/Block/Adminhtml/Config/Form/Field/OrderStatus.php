<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

/**
 * Frontend source model for order status select drop down
 */
class OrderStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Check if inheritance checkbox has to be rendered
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return false;
    }
}
