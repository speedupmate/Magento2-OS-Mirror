<?php

namespace Dotdigitalgroup\Sms\Plugin\Block\Checkout;

use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Magento\Checkout\Block\Checkout\LayoutProcessor as MageLayoutProcessor;
use Magento\Store\Model\StoreManagerInterface;

class LayoutProcessor
{
    /**
     * @var TransactionalSms
     */
    private $transactionalSmsConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LayoutProcessor constructor.
     * @param TransactionalSms $transactionalSmsConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransactionalSms $transactionalSmsConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->transactionalSmsConfig = $transactionalSmsConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param MageLayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(MageLayoutProcessor $subject, $jsLayout)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if (!$this->transactionalSmsConfig->isPhoneNumberValidationEnabled($storeId)) {
            return $jsLayout;
        }

        // @codingStandardsIgnoreStart
        $shippingConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
        $billingConfiguration = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

        if (isset($shippingConfiguration)) {
            $shippingConfiguration['telephone'] = $this->transactionalSmsConfig->telephoneFieldConfig("shippingAddress");
        }

        /* config: checkout/options/display_billing_address_on = payment_method */
        if (isset($billingConfiguration)) {
            foreach ($billingConfiguration as $key => &$element) {
                $method = substr($key, 0, -5);

                $element['dataScopePrefix'] = $this->transactionalSmsConfig->getDataScopePrefix("billingAddress", $method);
                $element['children']['form-fields']['children']['telephone'] = $this->transactionalSmsConfig->telephoneFieldConfig("billingAddress", $method);
            }
        }

        /* config: checkout/options/display_billing_address_on = payment_page */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form'])) {
            $method = 'shared';

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['telephone'] = $this->transactionalSmsConfig->telephoneFieldConfig("billingAddress", $method);
        }
        // @codingStandardsIgnoreEnd

        return $jsLayout;
    }
}
