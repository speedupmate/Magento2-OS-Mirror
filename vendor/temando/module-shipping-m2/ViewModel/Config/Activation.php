<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\Config;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Temando\Shipping\Model\Config\ModuleConfigInterface;

/**
 * View model for activation notice page.
 *
 * @package Temando\Shipping\ViewModel
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.temando.com/
 */
class Activation implements ArgumentInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * Activation constructor.
     * @param UrlInterface $urlBuilder
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Check if merchant registered an account already.
     *
     * @return bool
     */
    public function isMerchantRegistered()
    {
        return $this->moduleConfig->isRegistered();
    }

    /**
     * Obtain the URL to the external onboarding page.
     *
     * @return string
     */
    public function getRegisterAccountUrl()
    {
        return $this->moduleConfig->getRegisterAccountUrl();
    }

    /**
     * Obtain the URL to redirect the user into the Shipping Portal account.
     *
     * @return string
     */
    public function getAccountRedirectUrl()
    {
        return $this->urlBuilder->getUrl('temando/configuration_portal/account');
    }
}
