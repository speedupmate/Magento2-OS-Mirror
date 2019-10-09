<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Temando\Shipping\Model\Config\ModuleConfigInterface;

/**
 * Temando Config Getting Started Info Block
 *
 * @package Temando\Shipping\Block
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 *
 * @api
 */
class GettingStarted extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/form/field/getting_started.phtml';

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * GettingStarted constructor.
     *
     * @param Context $context
     * @param ModuleConfigInterface $moduleConfig
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        ModuleConfigInterface $moduleConfig,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $html = sprintf(
            '<td colspan="%d" id="%s">%s</td>',
            3 + (int)$this->_isInheritCheckboxRequired($element),
            $element->getHtmlId(),
            $this->_renderValue($element)
        );

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render element value
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Check if merchant registered an account at Temando.
     *
     * @return bool
     */
    public function isMerchantRegistered(): bool
    {
        return $this->moduleConfig->isRegistered();
    }

    /**
     * Obtain the URL to redirect the user into the Shipping Portal, locations section.
     *
     * @return string
     */
    public function getLocationsRedirectUrl(): string
    {
        return $this->_urlBuilder->getUrl('temando/configuration_portal/location');
    }

    /**
     * Obtain the URL to redirect the user into the Shipping Portal, carriers section.
     *
     * @return string
     */
    public function getCarriersRedirectUrl(): string
    {
        return $this->_urlBuilder->getUrl('temando/configuration_portal/carriers');
    }

    /**
     * Obtain packages configuration page URL.
     *
     * @return string
     */
    public function getPackagesUrl(): string
    {
        return $this->_urlBuilder->getUrl('temando/configuration_packaging/index');
    }

    /**
     * Obtain Shipping Portal Url.
     *
     * @deprecated since 1.6.0 | No longer used.
     * @return string
     */
    public function getShippingPortalUrl(): string
    {
        return $this->moduleConfig->getShippingPortalUrl();
    }

    /**
     * Obtain the URL to redirect the user into the Shipping Portal, experiences section.
     *
     * @return string
     */
    public function getExperienceRedirectUrl(): string
    {
        return $this->_urlBuilder->getUrl('temando/configuration_portal/experience');
    }
}
