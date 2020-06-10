<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Block\Adminhtml\Config\Form\Field\FieldSource;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\AppInterface;
use Vertex\Tax\Model\CollatorFactory;

/**
 * Converts Flex Field Source options into flex-field-select options
 */
class OptionProvider
{
    /** @var CollatorFactory */
    private $collatorFactory;

    /** @var Session */
    private $authSession;

    public function __construct(
        Session $authSession,
        CollatorFactory $collatorFactory
    ) {
        $this->authSession = $authSession;
        $this->collatorFactory = $collatorFactory;
    }

    /**
     * Convert Flex Field Source options into flex-field-select options
     *
     * @param array $sourceOptions
     * @return array
     */
    public function getOptions(array $sourceOptions) : array
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
    private function getSortedOptions(array $sourceOptions) : array
    {
        $collator = $this->collatorFactory->create($this->getUserLocale());
        $options = $sourceOptions;

        usort(
            $options,
            static function ($optionA, $optionB) use ($collator) {
                if ($optionA['value'] === 'none') {
                    return -1;
                }
                if ($optionB['value'] === 'none') {
                    return 1;
                }
                return $collator->compare($optionA['label'], $optionB['label']);
            }
        );
        return $options;
    }

    private function getUserLocale() : string
    {
        $userData = $this->authSession->getUser();
        return $userData ? $userData->getInterfaceLocale() : AppInterface::DISTRO_LOCALE_CODE;
    }
}
