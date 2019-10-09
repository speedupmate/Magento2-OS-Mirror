<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Block\Adminhtml\PageAction;

use Magento\Backend\Block\Widget\Button;

/**
 * Action Button to Save Product Attribute Mapping
 *
 * @api
 * @package Temando\Shipping\Block
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AttributeMappingSaveButton extends Button
{
    /**
     * Add button data
     *
     * @return Button
     */
    protected function _beforeToHtml()
    {
        $this->setData('label', __('Save Mapping'));
        $this->setData('class', 'save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
        $this->setData('id', 'save-mapping');
        $this->setData('level', 0);

        return parent::_beforeToHtml();
    }
}
