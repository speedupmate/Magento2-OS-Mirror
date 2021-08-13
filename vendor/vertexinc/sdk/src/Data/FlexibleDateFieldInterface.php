<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/**
 * Represents a Flexible Field containing a date as a value
 *
 * @api
 */
interface FlexibleDateFieldInterface extends FlexibleFieldInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \DateTimeInterface|null
     */
    public function getFieldValue();

    /**
     * {@inheritdoc}
     *
     * @param int $fieldId
     * @return FlexibleDateFieldInterface
     */
    public function setFieldId($fieldId);

    /**
     * {@inheritDoc}
     *
     * @param \DateTimeInterface $fieldValue
     * @return FlexibleDateFieldInterface
     */
    public function setFieldValue($fieldValue);
}
