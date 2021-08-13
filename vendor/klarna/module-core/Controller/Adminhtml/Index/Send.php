<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Controller\Adminhtml\Index;

use Klarna\Core\Model\Support\Email;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class Send extends Action
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Email
     */
    private $email;

    /**
     * @param Context          $context
     * @param RequestInterface $request
     * @param Email            $email
     * @codeCoverageIgnore
     */
    public function __construct(
        Context          $context,
        RequestInterface $request,
        Email $email
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->email = $email;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $postData = $this->request->getPostValue();
        $emailContent = $this->email->getTemplateContent($postData);

        try {
            $this->email->send($emailContent);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage("A error happened. Message: " . $e->getMessage());
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('*/*/support/form/new');
            return $redirect;
        }

        $this->messageManager->addSuccessMessage(__(
            "Thank you for your support request. We will come back to you as soon as possible."
        ));

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('*/*/support/form/new');
        return $redirect;
    }
}
