<?php

namespace Dotdigitalgroup\Email\Helper;

use Zend\Mail\Transport\SmtpOptions;

/**
 * Transactional emails configuration data values.
 */
class Transactional extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DDG_TRANSACTIONAL_ENABLED    = 'transactional_emails/ddg_transactional/enabled';
    const XML_PATH_DDG_TRANSACTIONAL_HOST       = 'transactional_emails/ddg_transactional/host';
    const XML_PATH_DDG_TRANSACTIONAL_USERNAME   = 'transactional_emails/ddg_transactional/username';
    const XML_PATH_DDG_TRANSACTIONAL_PASSWORD   = 'transactional_emails/ddg_transactional/password';
    const XML_PATH_DDG_TRANSACTIONAL_PORT       = 'transactional_emails/ddg_transactional/port';
    const XML_PATH_DDG_TRANSACTIONAL_DEBUG      = 'transactional_emails/ddg_transactional/debug';

    /**
     * Transactional constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @var \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Is transactional email enabled.
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DDG_TRANSACTIONAL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get transactional email host.
     *
     * @param int $storeId
     *
     * @return boolean|string
     */
    public function getSmtpHost($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DDG_TRANSACTIONAL_HOST,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get smtp username.
     *
     * @param int $storeId
     *
     * @return boolean|string
     */
    private function getSmtpUsername($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DDG_TRANSACTIONAL_USERNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get smtp password.
     *
     * @param int $storeId
     *
     * @return boolean|string
     */
    private function getSmtpPassword($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DDG_TRANSACTIONAL_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get smtp port.
     *
     * @param int $storeId
     *
     * @return boolean|string
     */
    private function getSmtpPort($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DDG_TRANSACTIONAL_PORT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get transactional log enabled.
     *
     * @param int $storeId
     *
     * @return bool
     */
    private function isDebugEnabled($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DDG_TRANSACTIONAL_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config values for transport.
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getTransportConfig($storeId)
    {
        $config = [
            'port' => $this->getSmtpPort($storeId),
            'auth' => 'login',
            'username' => $this->getSmtpUsername($storeId),
            'password' => $this->getSmtpPassword($storeId),
            'ssl' => 'tls',
        ];

        if ($this->isDebugEnabled($storeId)) {
            $this->_logger->debug('Mail transport config : ' . implode(',', $config));
        }

        return $config;
    }

    /**
     * @param int $storeId
     *
     * @return SmtpOptions
     */
    public function getSmtpOptions($storeId)
    {
        return new SmtpOptions(
            [
                'host' => $this->getSmtpHost($storeId),
                'port' => $this->getSmtpPort($storeId),
                'connection_class' => 'login',
                'connection_config' =>
                [
                    'username' => $this->getSmtpUsername($storeId),
                    'password' => $this->getSmtpPassword($storeId),
                    'ssl' => 'tls'
                ]
            ]
        );
    }

    /**
     * Check if the template code is containing dotmailer.
     *
     * @param string $templateCode
     * @return bool
     */
    public function isDotmailerTemplate($templateCode)
    {
        preg_match("/\_\d{1,10}$/", $templateCode, $matches);

        if (count($matches)) {
            return true;
        }

        return false;
    }
}
