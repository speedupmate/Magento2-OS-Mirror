<?php

namespace Dotdigitalgroup\Sms\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;

class FrontendAssets extends Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'Dotdigitalgroup_Sms::frontend_assets.phtml';

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
