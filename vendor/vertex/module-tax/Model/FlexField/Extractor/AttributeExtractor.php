<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use Magento\Framework\Exception\NotFoundException;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * Extract Attributes from class
 */
class AttributeExtractor
{
    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var ExceptionLogger */
    private $logger;

    /** @var TypeExtractor */
    private $typeExtractor;

    /**
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param TypeExtractor $typeExtractor
     * @param ExceptionLogger $logger
     */
    public function __construct(
        FlexFieldProcessableAttributeFactory $attributeFactory,
        TypeExtractor $typeExtractor,
        ExceptionLogger $logger
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->typeExtractor = $typeExtractor;
        $this->logger = $logger;
    }

    /**
     * Retrieve all attributes
     *
     * This creates an attribute data object for every getter on an object type, minus any already listed in the
     * blacklist.
     *
     * We use this in several places to allow for the easy import of data from interfaces, without having to
     * hard-code all the various attributes and their getters.
     *
     * @param string $className Class to pull getter attributes from
     * @param string $prefix Prefix to use on generated attribute codes
     * @param string $optionGroup Option Group to attach the generated attribute to
     * @param string $processor Class to use for processing the attribute
     * @param string[] $blacklist Methods in the provided class to prevent generation for
     * @return FlexFieldProcessableAttribute[]
     */
    public function extract($className, $prefix, $optionGroup, $processor, array $blacklist = [])
    {
        $methods = array_filter(
            get_class_methods($className),
            static function ($methodName) use ($blacklist) {
                return !in_array($methodName, $blacklist, true) && strpos($methodName, 'get') === 0;
            }
        );
        $attributes = [];
        foreach ($methods as $method) {
            try {
                $type = $this->typeExtractor->extract($className, $method);
            } catch (NotFoundException $exception) {
                $this->logger->error($exception);
                continue;
            }

            /** @var FlexFieldProcessableAttribute $attribute */
            $attribute = $this->attributeFactory->create();
            $attributeCode = $prefix . '.' . $method;
            $attribute->setAttributeCode($attributeCode);
            $attribute->setLabel(__(substr(preg_replace('/[A-Z]/', ' $0', $method), 4))->render());
            $attribute->setOptionGroup(__($optionGroup)->render());
            $attribute->setType($type);
            $attribute->setProcessor($processor);
            $attributes[$attributeCode] = $attribute;
        }

        return $attributes;
    }

    /**
     * Retrieve all date attributes
     *
     * This creates an attribute data object for every getter on an object type specified in `$dateFields`
     *
     * @param string $prefix Prefix to use on generated attribute codes
     * @param string[] $dateFields Methods in the provided class to generate attributes for
     * @param string $optionGroup Option Group to attach the generated attribute to
     * @param string $processor Class to use for processing the attribute
     * @return FlexFieldProcessableAttribute[]
     */
    public function extractDateFields($prefix, array $dateFields, $optionGroup, $processor)
    {
        $attributes = [];

        foreach ($dateFields as $dateField) {
            $attributeCode = $prefix . ".{$dateField}";
            /** @var FlexFieldProcessableAttribute $attribute */
            $attribute = $this->attributeFactory->create();
            $attribute->setAttributeCode($attributeCode);
            $attribute->setLabel(__(substr(preg_replace('/[A-Z]/', ' $0', $dateField), 4))->render());
            $attribute->setOptionGroup(__($optionGroup)->render());
            $attribute->setType(FlexibleFieldSource::TYPE_DATE);
            $attribute->setProcessor($processor);
            $attributes[$attributeCode] = $attribute;
        }

        return $attributes;
    }
}
