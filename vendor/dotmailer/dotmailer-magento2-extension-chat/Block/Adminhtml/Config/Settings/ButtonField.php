<?php

namespace Dotdigitalgroup\Chat\Block\Adminhtml\Config\Settings;

use Dotdigitalgroup\Chat\Model\Config;
use Dotdigitalgroup\Email\Helper\Data;
use Dotdigitalgroup\Email\Helper\OauthValidator;
use Magento\Backend\Block\Widget\Button;

abstract class ButtonField extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var OauthValidator
     */
    private $oauthValidator;

    /**
     * ButtonField constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Config $config
     * @param Data $helper
     * @param OauthValidator $oauthValidator
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Config $config,
        Data $helper,
        OauthValidator $oauthValidator,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->config = $config;
        $this->oauthValidator = $oauthValidator;
        parent::__construct($context, $data);
    }

    /**
     * Returns the class name based on API Creds validation
     * @return string
     */
    public function getCssClass()
    {
        if ($this->config->getApiSpaceId()) {
            return 'ddg-enabled-button';
        }
        return 'ddg-disabled-button';
    }

    /**
     * @return string
     */
    abstract protected function getButtonUrl();

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getLayout()
            ->createBlock(Button::class)
            ->setType('button')
            ->setLabel(__('Configure'))
            ->setOnClick(sprintf("window.open('%s','_blank')", $this->getButtonUrl()))
            ->setData('class', $this->getCssClass())
            ->toHtml();
    }

    /**
     * Removes use Default Checkbox
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param $url
     * @return string
     */
    protected function getEcAuthorisedUrl($url)
    {
        return $this->oauthValidator->createAuthorisedEcUrl($url, 'false');
    }
}
