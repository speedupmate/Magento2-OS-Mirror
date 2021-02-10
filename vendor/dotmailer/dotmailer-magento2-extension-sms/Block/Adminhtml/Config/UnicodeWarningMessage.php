<?php

namespace Dotdigitalgroup\Sms\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;

class UnicodeWarningMessage extends Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'Dotdigitalgroup_Sms::unicode_detection_message.phtml';

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
