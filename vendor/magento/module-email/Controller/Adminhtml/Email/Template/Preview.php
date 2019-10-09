<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

use Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Rendering email template preview.
 */
class Preview extends Template
{
    /**
     * Preview transactional email action.
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Preview'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred. The email template can not be opened for preview.')
            );
            $this->_redirect('adminhtml/*/');
        }
    }
}
