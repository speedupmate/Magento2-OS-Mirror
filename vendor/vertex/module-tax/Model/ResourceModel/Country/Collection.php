<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\ResourceModel\Country;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

/**
 *  Country Resource Collection
 */
class Collection extends CountryCollection
{
    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArrayISO3()
    {
        $options = $this->_toOptionArray('country_id', 'name', ['title' => 'iso3_code']);
        $sort = [];
        foreach ($options as $data) {
            $name = (string)$this->_localeLists->getCountryTranslation($data['value']);
            if (!empty($name)) {
                $sort[$name] = $data['title'];
            }
        }
        $this->_arrayUtils->ksortMultibyte($sort, $this->_localeResolver->getLocale());
        $sort = array_flip($sort);
        $options = [];
        foreach ($sort as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }
}
