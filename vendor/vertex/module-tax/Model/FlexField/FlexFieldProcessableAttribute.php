<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField;

/**
 * Data model for a Flex Field Processable Attribute
 *
 * @api
 * @since 3.2.0
 */
class FlexFieldProcessableAttribute
{
    /** Code-type flexible field */
    const TYPE_CODE = 'code';

    /** Date-type flex field */
    const TYPE_DATE = 'date';

    /** Numeric-type flex field */
    const TYPE_NUMERIC = 'numeric';

    /** @var string */
    private $attributeCode;

    /** @var string */
    private $label;

    /** @var string */
    private $optionGroup;

    /** @var string */
    private $type;

    /** @var string */
    private $processor;

    /**
     * Retrieve the attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * Retrieve Label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Retrieve option group
     *
     * @return string
     */
    public function getOptionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * Retrieve processor
     *
     * @return string
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Retrieve type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the attribute code
     *
     * @param string $attributeCode
     * @return FlexFieldProcessableAttribute
     */
    public function setAttributeCode(string $attributeCode)
    {
        $this->attributeCode = $attributeCode;

        return $this;
    }

    /**
     * Set the label
     *
     * @param string $label
     * @return FlexFieldProcessableAttribute
     */
    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the option group
     *
     * @param string $optionGroup
     * @return FlexFieldProcessableAttribute
     */
    public function setOptionGroup(string $optionGroup)
    {
        $this->optionGroup = $optionGroup;

        return $this;
    }

    /**
     * Set processor
     *
     * @param string $processor
     * @return FlexFieldProcessableAttribute
     */
    public function setProcessor(string $processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * Set the type
     *
     * @param string $type
     * @return FlexFieldProcessableAttribute
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }
}
