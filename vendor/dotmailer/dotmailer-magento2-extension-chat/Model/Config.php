<?php

namespace Dotdigitalgroup\Chat\Model;

use Dotdigitalgroup\Email\Helper\Config as EmailConfig;
use Dotdigitalgroup\Email\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_LIVECHAT_ENABLED = 'chat_api_credentials/settings/enabled';
    const XML_PATH_LIVECHAT_API_SPACE_ID = 'chat_api_credentials/credentials/api_space_id';
    const XML_PATH_LIVECHAT_API_TOKEN = 'chat_api_credentials/credentials/api_token';

    const CHAT_PORTAL_URL = 'WebChat';
    const CHAT_CONFIGURE_TEAM_PATH = 'team/users/all';
    const CHAT_CONFIGURE_WIDGET_PATH = 'account/chat-settings';

//    const MAGENTO_ROUTE = 'connector/email/accountcallback';
    const MAGENTO_PROFILE_CALLBACK_ROUTE = 'ec_chat/profile?isAjax=true';

    /**
     * Cookie used to get chat profile ID
     */
    const COOKIE_CHAT_PROFILE = 'ddg_chat_profile_id';

    /**
     * Paths which should have their values encrypted
     */
    const ENCRYPTED_CONFIG_PATHS = [
        self::XML_PATH_LIVECHAT_API_TOKEN,
        EmailConfig::XML_PATH_CONNECTOR_API_PASSWORD,
    ];

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $portalUrl;

    /**
     * @var string
     */
    private $scopeInterface = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

    /**
     * @var int
     */
    private $websiteId = 0;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EmailConfig
     */
    private $emailConfig;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var State
     */
    private $state;

    /**
     * Config constructor
     *
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     * @param ReinitableConfigInterface $reinitableConfig
     * @param WriterInterface $configWriter
     * @param SessionFactory $sessionFactory
     * @param UrlInterface $urlBuilder
     * @param EmailConfig $emailConfig
     * @param Data $helper
     * @param State $state
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        ReinitableConfigInterface $reinitableConfig,
        WriterInterface $configWriter,
        SessionFactory $sessionFactory,
        UrlInterface $urlBuilder,
        EmailConfig $emailConfig,
        Data $helper,
        State $state
    ) {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->reinitableConfig = $reinitableConfig;
        $this->configWriter = $configWriter;
        $this->sessionFactory = $sessionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->emailConfig = $emailConfig;
        $this->helper = $helper;
        $this->state = $state;

        $this->setScopeAndWebsiteId();
    }

    /**
     * @return mixed
     */
    public function getApiSpaceId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LIVECHAT_API_SPACE_ID,
            $this->scopeInterface,
            (string) $this->websiteId
        );
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LIVECHAT_API_TOKEN,
            $this->scopeInterface,
            (string) $this->websiteId
        );
    }

    /**
     * @return \Dotdigitalgroup\Email\Model\Apiconnector\Client
     */
    public function getApiClient()
    {
        return $this->helper->getWebsiteApiClient($this->websiteId);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChatPortalUrl()
    {
        return $this->emailConfig->getRegionAwarePortalUrl() . self::CHAT_PORTAL_URL;
    }

    /**
     * @return string
     */
    public function getConfigureChatTeamUrl()
    {
        return $this->emailConfig->getRegionAwarePortalUrl() . self::CHAT_CONFIGURE_TEAM_PATH;
    }

    /**
     * @return string
     */
    public function getConfigureChatWidgetUrl()
    {
        return $this->emailConfig->getRegionAwarePortalUrl() . self::CHAT_CONFIGURE_WIDGET_PATH;
    }

    /**
     * @return string
     */
    public function getConfigureChatTeamButtonUrl()
    {
        return $this->urlBuilder->getUrl('dotdigitalgroup_chat/studio/team');
    }

    /**
     * @return string
     */
    public function getConfigureChatWidgetButtonUrl()
    {
        return $this->urlBuilder->getUrl('dotdigitalgroup_chat/studio/widget');
    }

    /**
     * Save chat API space ID
     *
     * @param string $apiSpaceId
     * @return $this
     */
    public function saveChatApiSpaceId(string $apiSpaceId)
    {
        $this->configWriter->save(
            self::XML_PATH_LIVECHAT_API_SPACE_ID,
            $apiSpaceId,
            $this->scopeInterface,
            $this->websiteId
        );
        return $this;
    }

    /**
     * Save chat API token
     *
     * @param string $token
     * @return $this
     */
    public function saveChatApiToken(string $token)
    {
        $this->configWriter->save(
            self::XML_PATH_LIVECHAT_API_TOKEN,
            $this->encryptor->encrypt($token),
            $this->scopeInterface,
            $this->websiteId
        );
        return $this;
    }

    /**
     * Reinitialise config object
     *
     * @return $this
     */
    public function reinitialiseConfig()
    {
        $this->reinitableConfig->reinit();
        return $this;
    }

    /**
     * Enable Engagement Cloud integration
     *
     * @return $this
     */
    public function enableEngagementCloud()
    {
        $this->configWriter->save(
            EmailConfig::XML_PATH_CONNECTOR_API_ENABLED,
            true,
            $this->scopeInterface,
            $this->websiteId
        );
        return $this;
    }

    /**
     * Enable or disable live chat
     *
     * @param $value
     * @return $this
     */
    public function setLiveChatStatus($value)
    {
        $this->configWriter->save(self::XML_PATH_LIVECHAT_ENABLED, $value, $this->scopeInterface, $this->websiteId);
        return $this;
    }

    /**
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session ?: $this->session = $this->sessionFactory->create();
    }

    /**
     * Determines if Chat is enabled or not
     *
     * @return bool
     */
    public function isChatEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LIVECHAT_ENABLED, $this->scopeInterface, $this->websiteId);
    }

    /**
     * Deletes only Api Space Id and Token
     */
    public function deleteChatApiCredentials()
    {
        if ($this->getApiSpaceId()) {
            $this->configWriter->delete(self::XML_PATH_LIVECHAT_API_SPACE_ID, $this->scopeInterface, $this->websiteId);
            $this->configWriter->delete(self::XML_PATH_LIVECHAT_API_TOKEN, $this->scopeInterface, $this->websiteId);
        }
    }

    /**
     * Sets the Scope level
     */
    private function setScopeAndWebsiteId()
    {
        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $website = $this->helper->getWebsiteForSelectedScopeInAdmin();
        } else {
            $website = $this->helper->getWebsite();
        }

        $this->scopeInterface = $website->getId()
            ? ScopeInterface::SCOPE_WEBSITES
            : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->websiteId = $website->getId();
    }
}
