<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Data;

/**
 * Represents a Flexible Field containing an alphanumeric string as a value
 *
 * @api
 */
interface FlexibleCodeFieldInterface extends FlexibleFieldInterface
{
    /**
     * {@inheritDoc}
     *
     * @return string|null
     */
    public function getFieldValue();

    /**
     * {@inheritdoc}
     *
     * @param int $fieldId
     * @return FlexibleCodeFieldInterface
     */
    public function setFieldId($fieldId);

    /**
     * {@inheritDoc}
     *
     * @param string $fieldValue
     * @return FlexibleCodeFieldInterface
     */
    public function setFieldValue($fieldValue);
}
