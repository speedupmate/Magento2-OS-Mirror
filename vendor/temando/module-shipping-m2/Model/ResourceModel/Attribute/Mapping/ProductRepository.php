<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Attribute\Mapping;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Model\Attribute\Mapping\ProductInterface;
use Temando\Shipping\Model\Attribute\Mapping\ProductInterfaceFactory;
use Temando\Shipping\Model\ResourceModel\Repository\AttributeMappingProductRepositoryInterface;

/**
 * Temando Product Attribute Mapping
 *
 * @package Temando\Shipping\Model
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ProductRepository implements AttributeMappingProductRepositoryInterface
{
    /**
     * @var Product
     */
    private $resource;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductRepository constructor.
     * @param Product $resource
     * @param ProductInterfaceFactory $productFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Product $resource,
        ProductInterfaceFactory $productFactory,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    /**
     * Get a product attribute mapping by node path.
     *
     * @param string $nodePath
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getByNodePathId($nodePath): ProductInterface
    {
        /** @var \Temando\Shipping\Model\Attribute\Mapping\Product $productMapping */
        $productMapping = $this->productFactory->create();
        $this->resource->load($productMapping, $nodePath);

        if (!$productMapping->getNodePathId()) {
            throw new NoSuchEntityException(
                __('Product attribute mapping for node path "%1" does not exist.', $nodePath)
            );
        }

        return $productMapping;
    }

    /**
     * Create a new product attribute mapping.
     *
     * @param array $data
     * @return int
     */
    private function create($data): int
    {
        try {
            return $this->resource->createNewProductAttributeMapping($data);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return 0;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Save a product attribute mapping.
     *
     * @param $data
     * @return ProductInterface
     * @throws CouldNotSaveException
     */
    public function save($data): ProductInterface
    {
        try {
            /** @var \Temando\Shipping\Model\Attribute\Mapping\Product $productAttributeMapping */
            $productAttributeMapping = $this->getByNodePathId($data['node_path_id']);
            $productAttributeMapping->setData($data);
        } catch (NoSuchEntityException $e) {
            if ($data['mapping_attribute_id'] && $this->resource->isAttributeAlreadyMapped(
                $data['mapping_attribute_id'],
                $data['node_path_id']
            )) {
                throw new CouldNotSaveException(
                    __(
                        '%1 is already mapped to another shipping attribute.',
                        $data['mapping_attribute_id']
                    )
                );
            }

            if ($this->create($data)) {
                return $this->productFactory->create($data);
            }

            throw new CouldNotSaveException(__('Unable to create attribute mapping.'));
        }

        if ($productAttributeMapping->getMappingAttributeId() && $this->resource->isAttributeAlreadyMapped(
            $productAttributeMapping->getMappingAttributeId(),
            $productAttributeMapping->getNodePathId()
        )) {
            throw new CouldNotSaveException(
                __(
                    '%1 is already mapped to another shipping attribute.',
                    $productAttributeMapping->getMappingAttributeId()
                )
            );
        }

        return $this->saveAttributeMapping($productAttributeMapping);
    }

    /**
     * Delete the product attribute mapping.
     *
     * @param string $nodePathId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete($nodePathId): bool
    {
        $mapping = $this->getByNodePathId($nodePathId);

        try {
            $this->resource->delete($mapping);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return false;
        }

        return true;
    }

    /**
     * Perform the save on a product attribute mapping.
     *
     * @param ProductInterface $productAttributeMapping
     * @return ProductInterface
     * @throws CouldNotSaveException
     */
    private function saveAttributeMapping(ProductInterface $productAttributeMapping): ProductInterface
    {
        try {
            $this->resource->save($productAttributeMapping);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Error saving product attribute mapping.'), $exception);
        }

        return $productAttributeMapping;
    }

    /**
     * Get array of available mapped attributes
     *
     * @return array
     */
    public function getMappedAttributes(): array
    {
        try {
            return $this->resource->getAllMappedAttributes();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return [];
        }
    }
}
