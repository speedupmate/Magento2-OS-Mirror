<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Hidden Input & Display for the Flexible Field ID in the Admin Configuration
 */
class FlexibleFieldId extends AbstractBlock
{
    /**
     * Retrieve HTML class
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->getData('class');
    }

    /**
     * Retrieve extra HTML parameters
     *
     * @return string|null
     */
    public function getExtraParams()
    {
        return $this->getData('extra_params');
    }

    /**
     * Retrieve unique ID
     *
     * @return string|null
     */
    public function getInputId()
    {
        return $this->getData('id');
    }

    /**
     * Retrieve name used when sending to server
     *
     * @return string|null
     */
    public function getInputName()
    {
        return $this->getData('name');
    }

    /**
     * Retrieve title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Retrieve the current value
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * Set the HTML Class
     *
     * @param string $class
     * @return FlexibleFieldId
     */
    public function setClass($class)
    {
        $this->setData('class', $class);
        return $this;
    }

    /**
     * Set the extra HTML parameters
     *
     * @param string $extraParams
     * @return FlexibleFieldId
     */
    public function setExtraParams($extraParams)
    {
        $this->setData('extra_params', $extraParams);
        return $this;
    }

    /**
     * Set the unique ID
     *
     * @param string $elementId
     * @return FlexibleFieldId
     */
    public function setInputId($elementId)
    {
        $this->setData('id', $elementId);
        return $this;
    }

    /**
     * Set the name when sending data to server
     *
     * @param string $name
     * @return FlexibleFieldId
     */
    public function setInputName($name)
    {
        $this->setData('name', $name);
        return $this;
    }

    /**
     * Set the title
     *
     * @param string $title
     * @return FlexibleFieldId
     */
    public function setTitle($title)
    {
        $this->setData('title', $title);
        return $this;
    }

    /**
     * Set the current value
     *
     * @param string $value
     * @return FlexibleFieldId
     */
    public function setValue($value)
    {
        $this->setData('value', $value);
        return $this;
    }

    /**
     * Render the input and label
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        $value = $this->getIsRenderToJsTemplate() ? '<%- field_id %>' : $this->escapeHtml($this->getValue());
        $inlineValue = $this->getIsRenderToJsTemplate() ? $value : $this->escapeHtmlAttr($this->getValue());

        $html = <<<HTML
<input type="hidden" name="{$this->getInputName()}" id="{$this->getInputId()}"
    class="{$this->getClass()}" title="{$this->escapeHtmlAttr($this->getTitle())}"
    value="{$inlineValue}" {$this->getExtraParams()} />
<span>{$value}</span>
HTML;
        return str_replace(["\r", "\n"], '', $html);
    }
}
