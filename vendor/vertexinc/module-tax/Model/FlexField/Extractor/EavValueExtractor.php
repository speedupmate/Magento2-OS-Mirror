<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use DateTimeImmutable;
use Exception;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Vertex\Tax\Model\DateTimeImmutableFactory;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Extract item value
 */
class EavValueExtractor
{
    /** @var DateTimeImmutableFactory */
    private $dateTimeFactory;

    /** @var Config */
    private $eavConfig;

    /** @var ExceptionLogger */
    private $logger;

    /** @var ValueExtractor */
    private $valueExtractor;

    /**
     * @param Config $eavConfig
     * @param DateTimeImmutableFactory $dateTimeFactory
     * @param ValueExtractor $valueExtractor
     * @param ExceptionLogger $logger
     */
    public function __construct(
        Config $eavConfig,
        DateTimeImmutableFactory $dateTimeFactory,
        ValueExtractor $valueExtractor,
        ExceptionLogger $logger
    ) {
        $this->eavConfig = $eavConfig;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->valueExtractor = $valueExtractor;
        $this->logger = $logger;
    }

    /**
     * Retrieve value from an object
     *
     * @param object $item Object to call method against
     * @param string $entityType Object EAV entity type
     * @param string $attributeCode Method name to retrieve value
     * @param string $prefix Prefix on attribute code
     * @param string[] $dateFields Fields that should be processed as a date
     * @return string|int|null|DateTimeImmutable
     */
    public function extract($item, $entityType, $attributeCode, $prefix, array $dateFields = [])
    {
        try {
            $optionGroup = $prefix . '.' . EavAttributeExtractor::CUSTOM_PREFIX . '.';

            if ($item instanceof CustomAttributesDataInterface && strpos($attributeCode, $optionGroup) === 0) {
                $attributeName = substr($attributeCode, strlen($optionGroup));
                $customAttribute = $item->getCustomAttribute($attributeName);
                $value = $customAttribute ? $customAttribute->getValue() : null;

                if ($value === null && $item instanceof DataObject && $item->hasData($attributeName)) {
                    /*
                     * In some cases, a custom attribute may be loaded into the product's join and stored with the rest
                     * of the attributes, but nothing properly places it into the custom attribute data.  (I'm looking
                     * at you, Customer Custom Attributes).  In this case, we can still load it via ->getData.
                     */
                    $value = $item->getData($attributeName);
                }

                if ($value === null) {
                    return null;
                }

                if (in_array($attributeName, $dateFields, true)) {
                    return $value ? $this->dateTimeFactory->create($value) : null;
                }

                return $this->processValue($entityType, $attributeName, $value);
            }

            return $this->valueExtractor->extract($item, $attributeCode, $prefix, $dateFields);
        } catch (Exception $e) {
            $this->logger->warning($e);
            return null;
        }
    }

    /**
     * Retrieve mapped option IDs with text options from a source model
     *
     * @param string $value
     * @param SourceInterface $source
     * @return string
     */
    private function getValueFromSource($value, $source)
    {
        if ($source instanceof Boolean) {
            // Keep boolean values integers
            return (int)$value;
        }

        $values = [];
        foreach (explode(',', $value) as $optionId) {
            $text = $source->getOptionText($optionId);

            if ($text !== false) {
                $values[] = $source->getOptionText($optionId);
            }
        }

        sort($values);
        return implode(',', $values);
    }

    /**
     * Process a value with a source model
     *
     * @param string $entityType Entity type ID
     * @param string $attributeCode Attribute code
     * @param string $value Attribute value
     * @return string|null
     */
    private function processValue($entityType, $attributeCode, $value)
    {
        try {
            $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
            return $attribute->usesSource() ? $this->getValueFromSource($value, $attribute->getSource()) : $value;
        } catch (LocalizedException $exception) {
            $this->logger->error($exception);
            return null;
        }
    }
}
