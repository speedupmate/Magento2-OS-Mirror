<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Extractor;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

/**
 * Extract custom attributes from class
 */
class EavAttributeExtractor
{
    const CUSTOM_PREFIX = 'custom';

    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var EavTypeExtractor */
    private $typeExtractor;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param EavTypeExtractor $typeExtractor
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        FlexFieldProcessableAttributeFactory $attributeFactory,
        EavTypeExtractor $typeExtractor,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->typeExtractor = $typeExtractor;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve a list of custom attributes
     *
     * @param $eavEntityCode
     * @param string[] $blacklist List of attributes to be excluded
     * @return AttributeInterface[]
     */
    private function getCustomAttributeList($eavEntityCode, array $blacklist = [])
    {
        $searchBuilder = $this->searchCriteriaBuilder;

        if (!empty($blacklist)) {
            $searchBuilder = $searchBuilder->addFilter('attribute_code', $blacklist, 'nin');
        };

        $searchCriteria = $searchBuilder
            ->addFilter('backend_type', 'static', 'neq')
            ->create();

        return $this->attributeRepository->getList($eavEntityCode, $searchCriteria)->getItems();
    }

    /**
     * Retrieve a list of custom date attribute codes
     *
     * @param string $eavEntityCode
     * @return array
     */
    public function getCustomDateAttributeCodes($eavEntityCode)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('backend_type', 'datetime')
            ->create();

        $attributes = $this->attributeRepository->getList($eavEntityCode, $searchCriteria)->getItems();

        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            $attributeCodes[] = $attribute->getAttributeCode();
        }
        return $attributeCodes;
    }

    /**
     * Retrieve all attributes
     *
     * This creates an attribute data object for every custom attribute object type.
     *
     * @param string $eavEntityCode Eav entity code
     * @param string $prefix Prefix to use on generated attribute codes
     * @param string $optionGroup Option Group to attach the generated attribute to
     * @param string $processor Class to use for processing the attribute
     * @param string[] $blacklist List of attributes to be excluded
     * @return FlexFieldProcessableAttribute[]
     */
    public function extract($eavEntityCode, $prefix, $optionGroup, $processor, array $blacklist = [])
    {
        $prefix .= '.' . self::CUSTOM_PREFIX;
        $customAttributes = $this->getCustomAttributeList($eavEntityCode, $blacklist);
        $attributes = [];

        foreach ($customAttributes as $eavAttribute) {
            /** @var Attribute $eavAttribute */
            $type = $this->typeExtractor->extract($eavAttribute);

            /** @var FlexFieldProcessableAttribute $attribute */
            $attribute = $this->attributeFactory->create();
            $attributeCode = $prefix . '.' . $eavAttribute->getAttributeCode();
            $attribute->setAttributeCode($attributeCode);
            $attribute->setLabel($eavAttribute->getDefaultFrontendLabel());
            $attribute->setOptionGroup(__($optionGroup)->render());
            $attribute->setType($type);
            $attribute->setProcessor($processor);
            $attributes[$attributeCode] = $attribute;
        }
        return $attributes;
    }
}
