<?php

namespace Dotdigitalgroup\Sms\Block\Adminhtml\Config;

use Dotdigitalgroup\Sms\Model\Account;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AccountMessage extends Field
{
    /**
     * @var Account
     */
    private $account;

    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'Dotdigitalgroup_Sms::account_message.phtml';

    /**
     * AccountMessage constructor.
     * @param Context $context
     * @param Account $account
     * @param array $data
     */
    public function __construct(
        Context $context,
        Account $account,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Exception
     */
    public function render(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return bool
     */
    public function shouldDisplayAccountMessage()
    {
        return !$this->account->canSendSmsInCurrentScope();
    }
}
