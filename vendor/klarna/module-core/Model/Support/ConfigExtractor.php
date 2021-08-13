<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model\Support;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigExtractor
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Extracts a configuration
     * It's possible to filter by substrings in path, scope type and scope code
     *
     * @param array    $contains
     * @param array    $notContains
     * @param string   $scopeType
     * @param int|null $scopeCode
     * @return array
     */
    public function getConfig(
        array $contains = [],
        array $notContains = [],
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeCode = null
    ): array {
        // get all config paths
        $rawConfig = $this->config->getValue('', $scopeType, $scopeCode);
        $items = $this->convertArrayToOneDimension($rawConfig);
        $array = $this->joinItems($items);

        foreach ($contains as $string) {
            $array = $this->filterContains($string, $array);
        }

        foreach ($notContains as $string) {
            $array = $this->filterNotContains($string, $array);
        }

        ksort($array);
        return $array;
    }

    /**
     * Filter array by string occurrence in key
     *
     * @param string $needle
     * @param array  $array
     * @return array
     */
    private function filterContains(string $needle, array $array): array
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (strpos($key, $needle) !== false) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Filter array by string non-occurrence in key
     *
     * @param string $needle
     * @param array  $array
     * @return array
     */
    private function filterNotContains(string $needle, array $array): array
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (strpos($key, $needle) === false) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Takes out the last element of each sub-array, which is the config value,
     * and joins the remainder items
     *
     * @param array $array
     * @return array
     */
    private function joinItems(array $array): array
    {
        $converted = [];

        foreach ($array as $item) {
            $value = array_pop($item);
            $key = join('/', $item);
            $converted[$key] = $value;
        }

        return $converted;
    }

    /**
     * The raw config array is transformed into a flat array
     *
     * @param array $array
     * @return array
     */
    private function convertArrayToOneDimension(array $array): array
    {
        $elements = [];
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $elements[] = [$key, $value];
                continue;
            }
            $subArray = $this->convertArrayToOneDimension($value);
            foreach ($subArray as $subItem) {
                array_unshift($subItem, $key);
                $elements[] = $subItem;
            }
        }
        return $elements;
    }
}
