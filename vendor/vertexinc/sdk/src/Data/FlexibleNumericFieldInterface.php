<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/**
 * Represents a Flexible Field containing a number as a value
 *
 * @api
 */
interface FlexibleNumericFieldInterface extends FlexibleFieldInterface
{
    /**
     * {@inheritDoc}
     *
     * @return number|null
     */
    public function getFieldValue();

    /**
     * {@inheritdoc}
     *
     * @param int $fieldId
     * @return FlexibleNumericFieldInterface
     */
    public function setFieldId($fieldId);

    /**
     * {@inheritDoc}
     *
     * @param number $fieldValue
     * @return FlexibleNumericFieldInterface
     */
    public function setFieldValue($fieldValue);
}
