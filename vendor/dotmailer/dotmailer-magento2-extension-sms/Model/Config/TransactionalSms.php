<?php

namespace Dotdigitalgroup\Sms\Model\Config;

use Magento\Directory\Model\AllowedCountries;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class TransactionalSms
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param RequestInterface $request
     * @param ReinitableConfigInterface $reinitableConfig
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        RequestInterface $request,
        ReinitableConfigInterface $reinitableConfig,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->request = $request;
        $this->reinitableConfig = $reinitableConfig;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if Transactional SMS > Enabled is set to Yes.
     *
     * @param int $storeId
     * @return bool
     */
    public function isSmsEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isPhoneNumberValidationEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_SMS_PHONE_NUMBER_VALIDATION,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @param $websiteId
     * @return bool
     */
    private function isSmsEnabledAtWebsiteLevel($websiteId)
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @param $storeId
     * @param $smsPath
     * @return bool
     */
    public function isSmsTypeEnabled($storeId, $smsPath)
    {
        return $this->scopeConfig->getValue(
            $smsPath,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * Switches off the global SMS enabled flag in the current scope,
     * and in any direct parent or child scopes.
     */
    public function forceSwitchOff()
    {
        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website', 0);

        if ($storeId) {
            $this->switchOffAtStoreLevel($storeId);
            $this->switchOffAtWebsiteLevel(
                $this->storeManager->getStore($storeId)->getWebsiteId()
            );
        } elseif ($websiteId) {
            $this->switchOffAtWebsiteLevel($websiteId);
            $this->switchOffForAllChildStores($websiteId);
        } elseif ($websiteId === 0) {
            $this->switchOffAtDefaultLevel();
        }

        $this->reinitableConfig->reinit();
    }

    /**
     * @param $storeId
     */
    private function switchOffAtStoreLevel($storeId)
    {
        $this->configWriter->save(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED,
            0,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * @param $websiteId
     */
    private function switchOffAtWebsiteLevel($websiteId)
    {
        $this->configWriter->save(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED,
            0,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     *
     */
    private function switchOffAtDefaultLevel()
    {
        $this->configWriter->save(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_ENABLED,
            0
        );
    }

    /**
     * @param $websiteId
     */
    private function switchOffForAllChildStores($websiteId)
    {
        $childStores = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        foreach ($childStores as $storeId) {
            if ($this->isSmsEnabled($storeId)) {
                $this->switchOffAtStoreLevel($storeId);
            }
        }
    }

    /**
     * Can be overridden via config.xml
     *
     * @return string
     */
    public function getBatchSize()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_TRANSACTIONAL_SMS_BATCH_SIZE
        );
    }

    /**
     * @param $websiteId
     * @return string
     */
    public function getPreferredCountry($websiteId)
    {
        return $this->scopeConfig->getValue(
            Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @param $websiteId
     * @return string
     */
    public function getAllowedCountries($websiteId)
    {
        return $this->scopeConfig->getValue(
            AllowedCountries::ALLOWED_COUNTRIES_PATH,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Prepare telephone field config according to the Magento default config
     * @param $addressType
     * @param string $method
     * @return array
     */
    public function telephoneFieldConfig($addressType, $method = '')
    {
        return  [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => $addressType . $method,
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'Dotdigitalgroup_Sms/form/element/telephone',
                'tooltip' => [
                    'description' => 'For SMS order notifications.',
                    'tooltipTpl' => 'ui/form/element/helper/tooltip'
                ],
            ],
            'dataScope' => $addressType . $method . '.telephone',
            'label' => __('Phone Number'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 120,
            'validation' => [
                "required-entry" => true,
                "max_text_length" => 255,
                "min_text_length" => 1,
                'validate-phone-number' => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'focused' => false,
        ];
    }

    /**
     * @param string $addressType
     * @param string $method
     * @return string
     */
    public function getDataScopePrefix($addressType, $method = '')
    {
        return $addressType . $method;
    }
}
