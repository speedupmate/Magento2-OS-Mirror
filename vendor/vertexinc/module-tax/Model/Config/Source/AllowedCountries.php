<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Vertex\Tax\Model\Config;

/**
 * Provides a list of countries indexed by the Vertex region they're associated with
 */
class AllowedCountries implements OptionSourceInterface
{
    /** @var Config */
    private $config;

    /** @var Collection */
    private $countryCollection;

    /**
     * @param Config $config
     * @param Collection $countryCollection
     */
    public function __construct(Config $config, Collection $countryCollection)
    {
        $this->config = $config;
        $this->countryCollection = $countryCollection;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        /**
         * @var array $return Format expected by toOptionArray
         * @var array $countries Indexed array of [label => Country Name, value => Country Code]
         * @var string[] $countryNames Mapped by 2-character ISO code
         * @var string[] $regionCountryMap Mapping of country codes (index) to their region name (value)
         * @var array $regionMap Array of pointers to entries on the {@see $return} variable, indexed by region name
         * @var string[] $regions List of regions
         */
        $return = [];
        $countries = $this->countryCollection->loadData()->toOptionArray();
        array_shift($countries);
        $countryNames = array_column($countries, 'label', 'value');
        asort($countryNames);
        $regionCountryMap = $this->getRegionMap();
        $regionMap = [];
        $regions = $this->getRegions();

        foreach ($regions as $region) {
            $index = count($return);
            $return[$index] = [
                'label' => __($region),
                'value' => [],
            ];
            $regionMap[$region] = &$return[$index];
        }

        foreach ($countryNames as $countryCode => $countryName) {
            $region = isset($regionCountryMap[$countryCode]) ? $regionCountryMap[$countryCode] : 'Others';

            $regionInReturn = &$regionMap[$region];
            $regionInReturn['value'][] = [
                'label' => __($countryName),
                'value' => $countryCode,
            ];
        }

        return $return;
    }

    /**
     * Retrieve a mapping of country codes to regions
     *
     * @return string[] Map of country codes (as index) to their region (as value)
     */
    private function getRegionMap()
    {
        $regions = $this->config->getListForAllowedCountrySort();
        $result = [];
        foreach ($regions as $region => $countries) {
            foreach ($countries as $country) {
                $result[$country] = $region;
            }
        }
        return $result;
    }

    /**
     * Retrieve a list of regions
     *
     * @return string[]
     */
    private function getRegions()
    {
        $regions = $this->config->getListForAllowedCountrySort();
        return array_keys($regions);
    }
}
