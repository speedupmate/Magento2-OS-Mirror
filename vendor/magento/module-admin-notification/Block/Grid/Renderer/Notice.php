<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

class Notice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<span class="grid-row-title">' .
            $row->getTitle() .
            '</span>' .
            ($row->getDescription() ? '<br />' .
            $row->getDescription() : '');
    }
}
