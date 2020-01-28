<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_ReCaptcha
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\ReCaptcha\Plugin\Block\Account;

use Magento\Customer\Block\Account\AuthenticationPopup;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings;
use Zend\Json\Json;

class AuthenticationPopupPlugin
{
    /**
     * @var LayoutSettings
     */
    private $layoutSettings;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param LayoutSettings $layoutSettings
     * @param Config|null $config
     */
    public function __construct(
        LayoutSettings $layoutSettings,
        Config $config
    ) {
        $this->layoutSettings = $layoutSettings;
        $this->config = $config;
    }

    /**
     * @param AuthenticationPopup $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsLayout(AuthenticationPopup $subject, $result)
    {
        $layout = Json::decode($result, Json::TYPE_ARRAY);

        if ($this->config->isEnabledFrontend()) {
            $layout['components']['authenticationPopup']['children']['msp_recaptcha']['settings']
                = $this->layoutSettings->getCaptchaSettings();
        }

        if (isset($layout['components']['authenticationPopup']['children']['msp_recaptcha'])
            && !$this->config->isEnabledFrontend()
        ) {
            unset($layout['components']['authenticationPopup']['children']['msp_recaptcha']);
        }

        return Json::encode($layout);
    }
}
