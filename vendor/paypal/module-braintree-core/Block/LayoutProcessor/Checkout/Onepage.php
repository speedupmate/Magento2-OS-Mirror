<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Block\LayoutProcessor\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Exception\InputException;

/**
 * Provides reCaptcha component configuration.
 */
class Onepage implements LayoutProcessorInterface
{
    /**
     * @var \Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface
     */
    private $captchaUiConfigResolver;

    /**
     * @var \Magento\ReCaptchaUi\Model\UiConfigResolverInterface
     */
    private $isCaptchaEnabled;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $jsLayout
     * @return array
     * @throws InputException
     */
    public function process($jsLayout)
    {
        if ($this->moduleManager->isEnabled('Magento_ReCaptchaUi')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->isCaptchaEnabled = $objectManager->create(
                'Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface'
            );
            $this->captchaUiConfigResolver = $objectManager->create(
                'Magento\ReCaptchaUi\Model\UiConfigResolverInterface'
            );
        } else {
            return $jsLayout;
        }

        $key = 'braintree';

        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']['braintree-recaptcha']['children']
            ['recaptcha_braintree']['settings'] = $this->captchaUiConfigResolver->get($key);
        } else {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children']['braintree-recaptcha']['children']['recaptcha_braintree'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children']['braintree-recaptcha']['children']['recaptcha_braintree']);
            }
        }

        return $jsLayout;
    }
}
