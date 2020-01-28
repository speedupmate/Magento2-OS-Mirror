<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/** @inheritDoc */
class FlexibleDateField implements FlexibleDateFieldInterface
{
    /** @var int */
    private $fieldId;

    /** @var \DateTimeInterface */
    private $value;

    /** @inheritDoc */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /** @inheritDoc */
    public function getFieldValue()
    {
        return $this->value;
    }

    /** @inheritDoc */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
        return $this;
    }

    /** @inheritDoc */
    public function setFieldValue($fieldValue)
    {
        $this->value = $fieldValue;
        return $this;
    }
}
