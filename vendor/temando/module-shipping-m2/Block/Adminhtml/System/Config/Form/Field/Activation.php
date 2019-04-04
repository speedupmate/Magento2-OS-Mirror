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
 * Temando Config Activation Info Block
 *
 * @package  Temando\Shipping\Block
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 *
 * @api
 */
class Activation extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/form/field/activation.phtml';

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * Activation constructor.
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
        if (!$this->getAuthorization()->isAllowed('Temando_Shipping::portal')) {
            return '';
        }

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
     * @return bool
     */
    public function isMerchantRegistered(): bool
    {
        return $this->moduleConfig->isRegistered();
    }

    /**
     * @return string
     */
    public function getRegisterAccountUrl(): string
    {
        return $this->moduleConfig->getRegisterAccountUrl();
    }

    /**
     * Obtain the URL to redirect the user into the Shipping Portal account.
     *
     * @return string
     */
    public function getAccountRedirectUrl(): string
    {
        return $this->_urlBuilder->getUrl('temando/configuration_portal/account');
    }
}
