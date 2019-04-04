<?php

namespace Dotdigitalgroup\Email\Controller\Adminhtml;

abstract class Automation extends \Magento\Backend\App\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotdigitalgroup_Email::automation');
    }
}
