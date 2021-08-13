<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Backend;

/**
 * Shared functionality among all Flexible Field backend classes
 */
class FlexibleFieldUtilities
{
    /**
     * Assemble all field ID entries
     *
     * @param array $loadedValues containing keys field_id and field_source for each entry
     * @param int $maxFieldId
     * @return array
     */
    public function assembleValues(array $loadedValues, $maxFieldId)
    {
        $result = [];
        for ($i = 1; $i <= $maxFieldId; ++$i) {
            $result[(string)$i] = [
                'field_id' => (string)$i,
                'field_source' => '',
            ];
        }
        foreach ($loadedValues as $row) {
            if (is_array($row) && $row['field_id']) {
                $result[$row['field_id']]['field_source'] = $row['field_source'];
            }
        }
        return $result;
    }

    /**
     * Remove the empty placeholder
     *
     * @param array& $value
     * @return void
     */
    public function removeEmpty(array &$value)
    {
        if (isset($value['__empty'])) {
            unset($value['__empty']);
        }
    }
}
