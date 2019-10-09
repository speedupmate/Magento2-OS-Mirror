<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field\FieldSource;

/**
 * Converts Flex Field Source options into flex-field-select options
 */
class OptionProvider
{
    /**
     * Convert Flex Field Source options into flex-field-select options
     *
     * @param array $sourceOptions
     * @return array
     */
    public function getOptions(array $sourceOptions): array
    {
        $options = $this->getSortedOptions($sourceOptions);
        foreach ($options as &$option) {
            if (is_array($option['value'])) {
                if (!$option['label']) {
                    $option['label'] = $option['value'];
                }
                $option['optgroup'] = $this->getSortedOptions($option['value']);
                $option['value'] = true;
                foreach ($option['optgroup'] as &$subOption) {
                    if (!$subOption['label']) {
                        $subOption['label'] = $subOption['value'];
                    }
                    $subOption['parent'] = $option['label'];
                }
            }
        }
        return $options;
    }

    /**
     * Sort the source options array
     *
     * @param array $sourceOptions
     * @return array
     */
    private function getSortedOptions(array $sourceOptions): array
    {
        $options = $sourceOptions;
        usort(
            $options,
            static function ($optionA, $optionB) {
                if ($optionA['value'] === 'none') {
                    return -1;
                }
                if ($optionB['value'] === 'none') {
                    return 1;
                }
                return strcmp($optionA['label'], $optionB['label']);
            }
        );
        return $options;
    }
}
