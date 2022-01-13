<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Math\Random;

/**
 * Class encode/decode Delivery Term configuration
 */
class DeliveryTerm
{
    /** @var Random */
    private $mathRandom;

    /**
     * @param Random $mathRandom
     */
    public function __construct(Random $mathRandom)
    {
        $this->mathRandom = $mathRandom;
    }

    /**
     * Make value readable by @see AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        if (!$this->isEncodedArrayFieldValue($value)) {
            return $this->encodeArrayFieldValue($value);
        }

        return $this->unserializeValue($value);
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }

        return $this->serializeValue($value);
    }

    /**
     * Decode value from used in @see AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    private function decodeArrayFieldValue(array $value)
    {
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('country_id', $row)
                || !array_key_exists('delivery_term', $row)
            ) {
                continue;
            }
            $countryId = $row['country_id'];
            $deliveryTerm = $row['delivery_term'];
            $result[$countryId] = $deliveryTerm;
        }

        return $result;
    }

    /**
     * Encode value to be used in @see AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $countryId => $deliveryTerm) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['country_id' => $countryId, 'delivery_term' => $deliveryTerm];
        }

        return $result;
    }

    /**
     * Check whether value is in form retrieved by @see encodeArrayFieldValue
     *
     * @param string|array $value
     * @return bool
     */
    private function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('country_id', $row)
                || !array_key_exists('delivery_term', $row)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param array $value
     * @return string
     */
    private function serializeValue($value)
    {
        if (is_array($value)) {
            $data = [];
            foreach ($value as $countryId => $deliveryTerm) {
                if (!array_key_exists($countryId, $data)) {
                    $data[$countryId] = $deliveryTerm;
                }
            }

            return json_encode($data, true);
        }

        return '';
    }

    /**
     * Create a value from a storable representation
     *
     * @param string|null $value
     * @return array
     */
    public function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return json_decode($value, true);
        }

        return [];
    }
}
