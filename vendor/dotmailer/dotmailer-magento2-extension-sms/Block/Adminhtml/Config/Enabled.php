<?php

namespace Dotdigitalgroup\Sms\Block\Adminhtml\Config;

use Dotdigitalgroup\Sms\Model\Account;
use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Enabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var TransactionalSms
     */
    private $config;

    /**
     * Enabled constructor.
     * @param Context $context
     * @param Account $account
     * @param TransactionalSms $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Account $account,
        TransactionalSms $config,
        array $data = []
    ) {
        $this->account = $account;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        if (!$this->account->canSendSmsInCurrentScope()) {
            $element->setData('disabled', 'disabled');
        }

        return parent::_getElementHtml($element);
    }
}
