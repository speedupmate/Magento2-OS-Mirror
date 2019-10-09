<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\PageAction;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Temando\Shipping\ViewModel\DataProvider\BatchUrl;

/**
 * Action Button to Batch View Page
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class BatchBackButton extends Button
{
    /**
     * @var BatchUrl
     */
    private $batchUrl;

    /**
     * BatchBackButton constructor.
     * @param Context $context
     * @param BatchUrl $batchUrl
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        BatchUrl $batchUrl,
        array $data = []
    ) {
        $this->batchUrl = $batchUrl;

        parent::__construct($context, $data);
    }

    /**
     * Add button data
     *
     * @return Button
     */
    protected function _beforeToHtml()
    {
        $backUrl = $this->batchUrl->getListActionUrl();
        $this->setData('label', __('Back'));
        $this->setData('class', 'back');
        $this->setData('id', 'back');
        $this->setData('level', 0);
        $this->setData('onclick', sprintf("setLocation('%s')", $backUrl));

        return parent::_beforeToHtml();
    }
}
