<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Temando\Shipping\Model\Config\ModuleConfigInterface;
use Temando\Shipping\ViewModel\Config\Activation as ViewModel;

/**
 * Temando Config Activation Info Block
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
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
     * @var ViewModel
     */
    private $viewModel;

    /**
     * Activation constructor.
     *
     * @param Context $context
     * @param ModuleConfigInterface $moduleConfig
     * @param ViewModel $viewModel
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        ModuleConfigInterface $moduleConfig,
        ViewModel $viewModel,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->viewModel = $viewModel;

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

        $this->setData('viewModel', $this->viewModel);

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
     * @deprecated | since 1.3.10
     * @see \Temando\Shipping\ViewModel\Config\Activation::isMerchantRegistered
     *
     * @return bool
     */
    public function isMerchantRegistered(): bool
    {
        return $this->viewModel->isMerchantRegistered();
    }

    /**
     * @deprecated | since 1.3.10
     * @see \Temando\Shipping\ViewModel\Config\Activation::getRegisterAccountUrl
     *
     * @return string
     */
    public function getRegisterAccountUrl(): string
    {
        return $this->viewModel->getRegisterAccountUrl();
    }

    /**
     * @deprecated | since 1.3.10
     * @see \Temando\Shipping\ViewModel\Config\Activation::getAccountRedirectUrl
     *
     * @return string
     */
    public function getAccountRedirectUrl(): string
    {
        return $this->viewModel->getAccountRedirectUrl();
    }
}
