<?php

namespace Dotdigitalgroup\Chat\Block\Adminhtml\Config\Settings;

class ConfigureTeamsButton extends ButtonField
{
    /**
     * Returns the URL to Configure Chat Teams
     * @return string
     */
    protected function getButtonUrl()
    {
        return $this->getEcAuthorisedUrl($this->config->getConfigureChatTeamUrl());
    }
}
