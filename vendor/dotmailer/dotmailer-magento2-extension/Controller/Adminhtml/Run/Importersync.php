<?php

namespace Dotdigitalgroup\Email\Controller\Adminhtml\Run;

class Importersync extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Dotdigitalgroup\Email\Model\ImporterFactory
     */
    private $importerFactory;

    /**
     * Importersync constructor.
     *
     * @param \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory
     * @param \Magento\Backend\App\Action\Context          $context
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->importerFactory = $importerFactory;
        $this->messageManager  = $context->getMessageManager();

        parent::__construct($context);
    }

    /**
     * Refresh suppressed contacts.
     *
     * @return null
     */
    public function execute()
    {
        /** @var \Dotdigitalgroup\Email\Model\Importer $importer */
        $importer = $this->importerFactory->create();
        $importer->processQueue();

        $this->messageManager->addSuccessMessage('Done.');

        $redirectBack = $this->_redirect->getRefererUrl();

        $this->_redirect($redirectBack);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotdigitalgroup_Email::config');
    }
}
