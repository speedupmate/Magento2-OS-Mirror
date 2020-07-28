<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CustomOptionFlexibleField as Model;
use Vertex\Tax\Model\Data\CustomOptionFlexibleFieldFactory as ModelFactory;
use Vertex\Tax\Model\Repository\CustomOptionFlexibleFieldRepository;
use Vertex\Tax\Model\ResourceModel\CustomOptionFlexibleField as ResourceModel;

/**
 * Performs loading and saving of the vertex_flex_field extension attribute for Product Custom Options
 *
 * @see ProductCustomOptionRepositoryInterface Intercepted Class
 */
class CustomOptionFlexFieldExtensionAttributeHandler
{
    /** @var Config */
    private $config;

    /** @var ModelFactory */
    private $modelFactory;

    /** @var CustomOptionFlexibleFieldRepository */
    private $repository;

    /** @var ResourceModel */
    private $resourceModel;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        ModelFactory $modelFactory,
        ResourceModel $resourceModel,
        StoreManagerInterface $storeManager,
        Config $config,
        CustomOptionFlexibleFieldRepository $repository
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->repository = $repository;
    }

    /**
     * Load Custom Option Flexible Field mapping onto a custom option
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param ProductCustomOptionInterface $customOption
     * @return ProductCustomOptionInterface
     * @see ProductCustomOptionRepositoryInterface::get() Intercepted method
     */
    public function afterGet(
        ProductCustomOptionRepositoryInterface $repository = null,
        ProductCustomOptionInterface $customOption
    ) {
        if (!$this->config->isVertexActive()) {
            // Early exit if module is unused
            return $customOption;
        }

        $flexibleFields = $this->loadFlexibleFields([$customOption]);

        if (!isset($flexibleFields[$customOption->getOptionId()])) {
            return $customOption;
        }

        $extensionAttributes = $customOption->getExtensionAttributes();
        $extensionAttributes->setVertexFlexField($flexibleFields[$customOption->getOptionId()]);

        return $customOption;
    }

    /**
     * Load Custom Option Flexible Field mappings on multiple custom options
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param ProductCustomOptionInterface[] $customOptions
     * @return ProductCustomOptionInterface[]
     * @see ProductCustomOptionRepositoryInterface::getList() Intercepted Method
     */
    public function afterGetList(ProductCustomOptionRepositoryInterface $repository, array $customOptions)
    {
        if (!$this->config->isVertexActive()) {
            // Early exit if module is unused
            return $customOptions;
        }

        $flexibleFields = $this->loadFlexibleFields($customOptions);
        foreach ($customOptions as $customOption) {
            if (!isset($flexibleFields[$customOption->getOptionId()])) {
                continue;
            }
            $flexibleFieldId = $flexibleFields[$customOption->getOptionId()]->getFlexFieldId();
            $customOption->getExtensionAttributes()->setVertexFlexField($flexibleFieldId);
        }

        return $customOptions;
    }

    /**
     * Load Custom Option Flexible Field mappings on multiple custom options
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param ProductCustomOptionInterface[] $customOptions
     * @return ProductCustomOptionInterface[]
     * @see ProductCustomOptionRepositoryInterface::getProductOptions() Intercepted method
     */
    public function afterGetProductOptions(
        ProductCustomOptionRepositoryInterface $repository,
        array $customOptions
    ) {
        if (!$this->config->isVertexActive()) {
            // Early exit if module is unused
            return $customOptions;
        }

        $flexibleFields = $this->loadFlexibleFields($customOptions);
        foreach ($customOptions as $customOption) {
            if (!isset($flexibleFields[$customOption->getOptionId()])) {
                continue;
            }
            $flexibleFieldId = $flexibleFields[$customOption->getOptionId()]->getFlexFieldId();
            $customOption->getExtensionAttributes()->setVertexFlexField($flexibleFieldId);
        }

        return $customOptions;
    }

    /**
     * Save Custom Option Flexible Field mappings
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param ProductCustomOptionInterface $customOption
     * @return ProductCustomOptionInterface
     * @see ProductCustomOptionRepositoryInterface::save() Intercepted Method
     */
    public function afterSave(
        ProductCustomOptionRepositoryInterface $repository,
        ProductCustomOptionInterface $customOption
    ) {
        if (!$this->config->isVertexActive() || !$customOption->getExtensionAttributes()) {
            // Early exit if module is unused or there are no extension attributes
            return $customOption;
        }

        $extensionAttributes = $customOption->getExtensionAttributes();
        $flexFieldId = $extensionAttributes->getVertexFlexField();

        $websiteId = $this->getWebsiteId($customOption);

        try {
            $flexField = $this->resourceModel->loadByOptionId($customOption->getOptionId(), $websiteId, false);
        } catch (NoSuchEntityException $exception) {
            if (!$flexFieldId) {
                // There was none to save and none found. We're in sync
                return $customOption;
            }
            $flexField = $this->modelFactory->create();
        }

        if (!$flexFieldId) {
            // We found a flex field but there was none included in save, so delete it
            $this->repository->delete($flexField);
        } else {
            $flexField->setWebsiteId($websiteId);
            $flexField->setFlexFieldId($flexFieldId);
            $flexField->setOptionId($customOption->getOptionId());
            $this->repository->save($flexField);
        }

        return $customOption;
    }

    /**
     * Delete flexible field mapping objects for a given Custom Option
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param callable $super
     * @param ProductCustomOptionInterface $option
     * @return bool
     * @see ProductCustomOptionRepositoryInterface::delete() Intercepted method
     */
    public function aroundDelete(
        ProductCustomOptionRepositoryInterface $repository,
        callable $super,
        ProductCustomOptionInterface $option
    ) {
        $result = $super($option);
        $this->repository->deleteByOptionId($option->getOptionId());
        return $result;
    }

    /**
     * Duplicate flexible field mapping objects on custom options for a given Product
     *
     * @param ProductCustomOptionRepositoryInterface $repository
     * @param callable $super
     * @param ProductInterface $originalProduct
     * @param ProductInterface $duplicateProduct
     * @return mixed
     * @see ProductCustomOptionRepositoryInterface::duplicate() Intercepted method
     */
    public function aroundDuplicate(
        ProductCustomOptionRepositoryInterface $repository,
        callable $super,
        ProductInterface $originalProduct,
        ProductInterface $duplicateProduct
    ) {
        $arguments = func_get_args();
        array_splice($arguments, 0, -2);
        $result = call_user_func_array($super, $arguments);

        $options = $originalProduct->getOptions();
        $option = is_array($options) && count($options) ? end($options) : null;

        if ($option === null // No options to duplicate
            || !$this->config->isVertexActive($this->getWebsiteId($option), ScopeInterface::SCOPE_WEBSITE)
        ) {
            return $result;
        }

        // We create some sort of unique hash map to tie the old options to the new ones, since that duplication
        // happens too low level for us to work with

        /** @var ProductCustomOptionInterface[] $originalOptionMap */
        $originalOptionMap = array_reduce(
            $repository->getProductOptions($originalProduct),
            function (array $carry, ProductCustomOptionInterface $option) {
                $carry[$this->getOptionHash($option)] = $option;
                return $carry;
            },
            []
        );

        /** @var ProductCustomOptionInterface[] $duplicateOptionMap */
        $duplicateOptionMap = array_reduce(
            $repository->getProductOptions($duplicateProduct),
            function (array $carry, ProductCustomOptionInterface $option) {
                $carry[$this->getOptionHash($option)] = $option;
                return $carry;
            },
            []
        );

        // With our hashes created, we simply grab the old and new IDs and put the database layer to work

        foreach ($originalOptionMap as $hash => $option) {
            if (!isset($duplicateOptionMap[$hash])) {
                continue;
            }

            $duplicateOption = $duplicateOptionMap[$hash];
            $this->resourceModel->duplicate($option->getOptionId(), $duplicateOption->getOptionId());
        }

        return $result;
    }

    /**
     * Retrieve a semi-unique hash to represent a custom option
     *
     * @param ProductCustomOptionInterface $customOption
     * @return string
     */
    private function getOptionHash(ProductCustomOptionInterface $customOption): string
    {
        return sha1(
            implode(
                '|',
                [
                    $customOption->getTitle(),
                    $customOption->getType(),
                    $customOption->getSku(),
                    $customOption->getPrice(),
                    $customOption->getPriceType()
                ]
            )
        );
    }

    /**
     * Retrieve the website id for the scope of a CustomOption
     *
     * @param ProductCustomOptionInterface $customOption
     * @return int
     */
    private function getWebsiteId(ProductCustomOptionInterface $customOption): int
    {
        if ($customOption instanceof Option && $customOption->getProduct()) {
            return (int)$customOption->getProduct()->getStore()->getWebsiteId();
        }
        if ($customOption instanceof Option) {
            $storeId = $customOption->getData('store_id');
            return $storeId === 0 ? $storeId : (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        }
        return (int)$this->storeManager->getStore()->getWebsiteId();
    }


    /**
     * Retrieve flexible field mapping objects given an array of Custom Option objects
     *
     * @param ProductCustomOptionInterface[] $customOptions Indexed by Option ID
     * @return Model[]
     */
    private function loadFlexibleFields(array $customOptions): array
    {
        $optionIds = array_map(
            static function (ProductCustomOptionInterface $customOption) {
                return $customOption->getOptionId();
            },
            $customOptions
        );

        $websiteIds = array_map(
            function (ProductCustomOptionInterface $customOption) {
                return $this->getWebsiteId($customOption);
            },
            $customOptions
        );

        return $this->resourceModel->loadForOptions($optionIds, $websiteIds);
    }
}
