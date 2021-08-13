<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Logs extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $rawFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        PageFactory $rawFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $rawFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Klarna_Core::logs');
        $resultPage->getConfig()->getTitle()->prepend(__('Klarna Logs'));
        return $resultPage;
    }
}
