<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Appends the optgroup multiselect option select feature
 */
class AllowedCountries extends Field
{
    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $html = parent::render($element);

        $jsLayout = json_encode(
            [
                '#' . $element->getHtmlId() => [
                    'Vertex_Tax/js/allowed-countries' => new \stdClass(), // Ensure its an object vs. array
                ],
            ]
        );

        return $html . '<script type="text/x-magento-init">' . $jsLayout . '</script>';
    }
}
