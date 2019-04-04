<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Model\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin allow omit email sending if option is disabled.
 */
class TransportInterfacePlugin
{
    /**
     * Config path to mail sending setting that shows if email communications are disabled.
     */
    const XML_PATH_SYSTEM_SMTP_DISABLE = 'system/smtp/disable';

    /**
     * Application config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Omit email sending if disabled.
     *
     * @param TransportInterface $subject
     * @param \Closure $proceed
     * @return void
     * @throws MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        \Closure $proceed
    ) {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_SYSTEM_SMTP_DISABLE, ScopeInterface::SCOPE_STORE)) {
            $proceed();
        }
    }
}
