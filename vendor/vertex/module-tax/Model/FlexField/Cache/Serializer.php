<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\FlexField\Cache;

use Magento\Framework\Serialize\SerializerInterface;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * Handle data exchange serialization for StorageInterface.
 */
class Serializer
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    public function __construct(
        SerializerInterface $serializer,
        FlexFieldProcessableAttributeFactory $attributeFactory
    ) {
        $this->serializer = $serializer;
        $this->attributeFactory = $attributeFactory;
    }

    public function unserialize(string $value): array
    {
        $attributesFromCache = $this->serializer->unserialize($value);
        $attributes = [];
        foreach ($attributes as $k => $attributeData) {
            $attributes[$k] = $this->revertProcessableAttributeToArrayAction($attributeData);
        }

        return $attributes;
    }

    public function serialize(array $attributes): string
    {
        $serializable = [];
        foreach ($attributes as $k => $attribute) {
            $serializable[$k] = $this->convertProcessableAttributeToArray($attribute);
        }

        return $this->serializer->serialize($serializable);
    }

    private function convertProcessableAttributeToArray(FlexFieldProcessableAttribute $attribute): array
    {
        return [
            'attributeCode' => $attribute->getAttributeCode(),
            'label' => $attribute->getLabel(),
            'optionGroup' => $attribute->getOptionGroup(),
            'type' => $attribute->getType(),
            'processor' => $attribute->getProcessor(),
        ];
    }

    private function revertProcessableAttributeToArrayAction(array $attributeData): FlexFieldProcessableAttribute
    {
        $attribute = $this->attributeFactory->create();
        $attribute->setAttributeCode($attributeData['attributeCode']);
        $attribute->setLabel($attributeData['label']);
        $attribute->setOptionGroup($attributeData['optionGroup']);
        $attribute->setType($attributeData['type']);
        $attribute->setProcessor($attributeData['processor']);

        return $attribute;
    }
}
