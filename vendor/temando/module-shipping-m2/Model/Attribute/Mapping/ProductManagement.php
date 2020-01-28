<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Attribute\Mapping;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Temando\Shipping\Api\Attribute\Mapping\ProductManagementInterface;
use Temando\Shipping\Model\Attribute\Mapping\Product\InputType;
use Temando\Shipping\Model\ResourceModel\Attribute\Mapping\Product\CollectionFactory as ProductCollectionFactory;
use Temando\Shipping\Model\ResourceModel\Attribute\Mapping\ProductRepository;

/**
 * Process product attribute mapping
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ProductManagement implements ProductManagementInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var InputType
     */
    private $inputType;

    /**
     * ProductManagement constructor.
     * @param CollectionFactory $collectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepository $repository
     * @param InputType $inputType
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepository $repository,
        InputType $inputType
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->repository = $repository;
        $this->inputType = $inputType;
    }

    /**
     * Retrieve shipping attributes.
     *
     * @return mixed[]
     */
    public function getShippingAttributes(): array
    {
        $productCollectionFactory = $this->productCollectionFactory->create();
        $attributes = $productCollectionFactory->load();

        $mappings = [];
        /** @var Product $attribute */
        foreach ($attributes as $attribute) {
            $mappings[] = $this->buildAttributeMappingArray($attribute);
        }

        return $mappings;
    }

    /**
     * Build the attribute mapping array used in the knockout component
     *
     * @param Product $attribute
     * @return array
     */
    private function buildAttributeMappingArray(Product $attribute): array
    {
        $isDefault = $attribute->getIsDefault();

        return [
            'id' => sprintf(
                "%s.%s",
                $isDefault
                    ? Product::NODE_PATH_PREFIX
                    : Product::NODE_PATH_PREFIX.'.'.Product::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX,
                $attribute->getNodePathId()
            ),
            'description' => $attribute->getDescription(),
            'mapped_attribute' => $attribute->getMappingAttributeId(),
            'is_default' => $isDefault
        ];
    }

    /**
     * Retrieve Magento product attributes
     *
     * @return mixed[]
     */
    public function getProductAttributes(): array
    {
        $attr = [
            [
                'code' => 'sku',
                'label' => 'Sku [sku]'
            ],
            [
                'code' => 'entity_id',
                'label' => 'Entity ID [entity_id]'
            ]
        ];

        $attributes = $this->collectionFactory->create();
        foreach ($attributes as $attribute) {
            if ($this->inputType->isAllowed($attribute)) {
                $attr[] = [
                    'code' => $attribute->getAttributeCode(),
                    'label' => $this->getProductAttributeLabel($attribute)
                ];
            }
        }

        uasort($attr, function ($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $attr;
    }

    /**
     * Get the product attribute label.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string
     */
    private function getProductAttributeLabel($attribute): string
    {
        return sprintf(
            '%s [%s]',
            $attribute->getFrontendLabel() ?: '-',
            $attribute->getAttributeCode()
        );
    }

    /**
     * Delete the product attribute mapping
     *
     * @param string $nodePathId
     * @return string
     */
    public function delete($nodePathId): string
    {
        $regex = sprintf(
            '/^(%s\.)+(%s\.)*/',
            Product::NODE_PATH_PREFIX,
            Product::NODE_PATH_CUSTOM_ATTRIBUTES_PREFIX
        );

        $nodePathId = preg_replace($regex, '', $nodePathId);

        try {
            $this->repository->delete($nodePathId);
        } catch (NoSuchEntityException $e) {
            return json_encode([
                'status' => 'NOK',
                'message' => $e->getMessage()
            ]);
        }

        return json_encode([
            'status' => 'OK',
            'message' => 'Successfully deleted attribute mapping'
        ]);
    }
}
