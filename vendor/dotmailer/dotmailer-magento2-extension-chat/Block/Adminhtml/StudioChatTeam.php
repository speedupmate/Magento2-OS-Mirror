<?php

namespace Dotdigitalgroup\Chat\Block\Adminhtml;

use Dotdigitalgroup\Email\Helper\OauthValidator;
use Dotdigitalgroup\Email\Block\Adminhtml\EngagementCloudEmbedInterface;
use Dotdigitalgroup\Chat\Model\Config;
use Magento\Backend\Block\Template\Context;

/**
 * @api
 */
class StudioChatTeam extends \Magento\Backend\Block\Template implements EngagementCloudEmbedInterface
{
    /**
     * @var OauthValidator
     */
    private $oauthValidator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param OauthValidator $oauthValidator
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        OauthValidator $oauthValidator,
        Config $config,
        array $data = []
    ) {
        $this->oauthValidator = $oauthValidator;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->oauthValidator->createAuthorisedEcUrl($this->config->getConfigureChatTeamUrl());
    }
}
