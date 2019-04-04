<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Config\Model\Config\Structure\ElementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Paypal\Helper\Backend as BackendHelper;
use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier;

class StructurePlugin
{
    /**
     * Request parameter name
     */
    const REQUEST_PARAM_COUNTRY = 'paypal_country';

    /**
     * @var BackendHelper
     */
    protected $_helper;

    /**
     * @var ScopeDefiner
     */
    protected $_scopeDefiner;

    /**
     * @var PaymentSectionModifier
     */
    private $paymentSectionModifier;

    /**
     * @var string[]
     */
    private static $_paypalConfigCountries = [
        'payment_us',
        'payment_ca',
        'payment_au',
        'payment_gb',
        'payment_jp',
        'payment_fr',
        'payment_it',
        'payment_es',
        'payment_hk',
        'payment_nz',
        'payment_de',
    ];

    /**
     * @param ScopeDefiner $scopeDefiner
     * @param BackendHelper $helper
     * @param PaymentSectionModifier|null $paymentSectionModifier
     */
    public function __construct(
        ScopeDefiner $scopeDefiner,
        BackendHelper $helper,
        PaymentSectionModifier $paymentSectionModifier = null
    ) {
        $this->_scopeDefiner = $scopeDefiner;
        $this->_helper = $helper;
        $this->paymentSectionModifier = $paymentSectionModifier
                                      ?: ObjectManager::getInstance()->get(PaymentSectionModifier::class);
    }

    /**
     * Get paypal configuration countries
     *
     * @param bool $addOther
     * @return string[]
     */
    public static function getPaypalConfigCountries($addOther = false)
    {
        $countries = self::$_paypalConfigCountries;
        if ($addOther) {
            $countries[] = 'payment_other';
        }
        return $countries;
    }

    /**
     * Substitute payment section with PayPal configs
     *
     * @param Structure $subject
     * @param \Closure $proceed
     * @param array $pathParts
     * @return ElementInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetElementByPathParts(
        Structure $subject,
        \Closure $proceed,
        array $pathParts
    ) {
        $isSectionChanged = $pathParts[0] == 'payment';
        if ($isSectionChanged) {
            $requestedCountrySection = 'payment_' . strtolower($this->_helper->getConfigurationCountryCode());
            if (in_array($requestedCountrySection, self::getPaypalConfigCountries())) {
                $pathParts[0] = $requestedCountrySection;
            } else {
                $pathParts[0] = 'payment_other';
            }
        }
        /** @var ElementInterface $result */
        $result = $proceed($pathParts);
        if ($isSectionChanged && isset($result)) {
            if ($result instanceof Section) {
                $this->restructurePayments($result);
                $result->setData(array_merge(
                    $result->getData(),
                    ['showInDefault' => true, 'showInWebsite' => true, 'showInStore' => true]
                ), $this->_scopeDefiner->getScope());
            }
        }
        return $result;
    }

    /**
     * Changes payment config structure.
     *
     * @param Section $result
     * @return void
     */
    private function restructurePayments(Section $result)
    {
        $sectionData = $result->getData();
        $sectionInitialStructure = isset($sectionData['children']) ? $sectionData['children'] : [];
        $sectionChangedStructure = $this->paymentSectionModifier->modify($sectionInitialStructure);
        $sectionData['children'] = $sectionChangedStructure;
        $result->setData($sectionData, $this->_scopeDefiner->getScope());
    }
}
