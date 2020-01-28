<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

use Magento\Framework\Exception\NotFoundException;
use Magento\Review\Controller\Adminhtml\Rating as RatingController;
use Magento\Framework\Controller\ResultFactory;

class Delete extends RatingController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ratingId = (int)$this->getRequest()->getParam('id');
        if ($ratingId) {
            try {
                /** @var \Magento\Review\Model\Rating $model */
                $model = $this->_objectManager->create(\Magento\Review\Model\Rating::class);
                $model->load($ratingId)->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the rating.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('review/rating/');
        return $resultRedirect;
    }
}
