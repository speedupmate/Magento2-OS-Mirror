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

namespace MSP\ReCaptcha\Block\Frontend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings;
use Zend\Json\Json;

class ReCaptcha extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LayoutSettings
     */
    private $layoutSettings;

    /**
     * @param Template\Context $context
     * @param null $decoder @deprecated
     * @param null $encoder @deprecated
     * @param LayoutSettings $layoutSettings
     * @param array $data
     * @param Config|null $config
     */
    public function __construct(
        Template\Context $context,
        $decoder,
        $encoder,
        LayoutSettings $layoutSettings,
        array $data = [],
        Config $config = null
    ) {
        parent::__construct($context, $data);
        $this->layoutSettings = $layoutSettings;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * Get public reCaptcha key
     * @return string
     */
    public function getPublicKey()
    {
        return $this->config->getPublicKey();
    }

    /**
     * Get current recaptcha ID
     */
    public function getRecaptchaId()
    {
        return (string) $this->getData('recaptcha_id') ?: 'msp-recaptcha-' . md5($this->getNameInLayout());
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $layout = Json::decode(parent::getJsLayout(), Json::TYPE_ARRAY);

        if ($this->config->isEnabledFrontend()) {
            // Backward compatibility with fixed scope name
            if (isset($layout['components']['msp-recaptcha'])) {
                $layout['components'][$this->getRecaptchaId()] = $layout['components']['msp-recaptcha'];
                unset($layout['components']['msp-recaptcha']);
            }

            $recaptchaComponentSettings = [];
            if (isset($layout['components'][$this->getRecaptchaId()]['settings'])) {
                $recaptchaComponentSettings = $layout['components'][$this->getRecaptchaId()]['settings'];
            }
            $layout['components'][$this->getRecaptchaId()]['settings'] = array_replace_recursive(
                $this->layoutSettings->getCaptchaSettings(),
                $recaptchaComponentSettings
            );

            $layout['components'][$this->getRecaptchaId()]['reCaptchaId'] = $this->getRecaptchaId();
        }

        return Json::encode($layout);
    }
    
    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->config->isEnabledFrontend()) {
            return '';
        }

        return parent::toHtml();
    }
}
