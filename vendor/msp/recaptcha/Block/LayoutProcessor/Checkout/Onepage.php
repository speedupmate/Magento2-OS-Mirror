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

namespace MSP\ReCaptcha\Block\LayoutProcessor\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\ObjectManager;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings;

class Onepage implements LayoutProcessorInterface
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
     * Onepage constructor.
     *
     * @param LayoutSettings $layoutSettings
     * @param Config|null $config
     */
    public function __construct(
        LayoutSettings $layoutSettings,
        Config $config = null
    ) {
        $this->layoutSettings = $layoutSettings;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->config->isEnabledFrontend()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['customer-email']['children']
            ['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['customer-email']['children']
            ['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();

            $jsLayout['components']['checkout']['children']['authentication']['children']
            ['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();
        } else {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['customer-email']['children']['msp_recaptcha'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['customer-email']['children']['msp_recaptcha']);
            }
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['customer-email']['children']['msp_recaptcha'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['customer-email']['children']['msp_recaptcha']);
            }
            if (isset($jsLayout['components']['checkout']['children']['authentication']['children']['msp_recaptcha'])) {
                unset($jsLayout['components']['checkout']['children']['authentication']['children']['msp_recaptcha']);
            }
        }

        return $jsLayout;
    }
}
