<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends Fieldset
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element) : string
    {
        $groupConfig = $element->getGroup();

        if (!empty($groupConfig['more_url']) && !empty($element->getComment())) {
            $comment = $element->getComment();
            $moreUrl = $this->escapeUrl($groupConfig['more_url']);
            $comment .= '<p><a href="' . $moreUrl . '" target="_blank" rel="noopener noreferrer">' .
                $this->escapeHtml(__('Learn more')) . '</a></p>';
            $element->setComment($comment);
        }

        return parent::_getHeaderCommentHtml($element);
    }
}
