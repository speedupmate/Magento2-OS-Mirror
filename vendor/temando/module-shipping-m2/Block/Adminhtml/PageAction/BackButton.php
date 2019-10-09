<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\PageAction;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\UrlInterface;

/**
 * Action Button to Referral Page
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BackButton extends Button
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * BackButton constructor.
     * @param Context $context
     * @param RedirectInterface $redirect
     * @param UrlInterface $urlBuilder
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        RedirectInterface $redirect,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->redirect = $redirect;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $data);
    }

    /**
     * Add button data
     *
     * @return Button
     */
    protected function _beforeToHtml()
    {
        $backUrl = $this->redirect->getRefererUrl();

        // default to admin dashboard if no referral url is present.
        if ($backUrl === $this->urlBuilder->getUrl()) {
            $backUrl = $this->urlBuilder->getUrl('admin');
        }

        $this->setData('label', __('Back'));
        $this->setData('class', 'back');
        $this->setData('id', 'back');
        $this->setData('onclick', sprintf("setLocation('%s')", $backUrl));

        return parent::_beforeToHtml();
    }
}
