<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Store\Test\Fixture\Store;

/**
 * Precondition:
 * 1. Category is created.
 * 2. Product is created and assigned to created category.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS -> Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts.
 *
 * @group Products_(MX)
 * @ZephyrId MAGETWO-23544, MAGETWO-21125
 */
class UpdateSimpleProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    protected $editProductPage;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        TestStepFactory $testStepFactory
    ) {
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Run update product simple entity test.
     *
     * @param CatalogProductSimple $initialProduct
     * @param CatalogProductSimple $product
     * @param Store|null $store
     * @param string $configData
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function test(
        CatalogProductSimple $initialProduct,
        CatalogProductSimple $product,
        Store $store = null,
        $configData = ''
    ) {
        $this->configData = $configData;
        // Preconditions
        $initialProduct->persist();
        $initialCategory = $initialProduct->hasData('category_ids')
            ? $initialProduct->getDataFieldConfig('category_ids')['source']->getCategories()[0]
            : null;
        $category = $product->hasData('category_ids') && $product->getCategoryIds()[0]
            ? $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]
            : $initialCategory;

        if ($store) {
            $store->persist();
            $productName[$store->getStoreId()] = $product->getName();
        }

        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        // Steps
        $filter = ['sku' => $initialProduct->getSku()];

        $this->productGrid->open();
        $this->productGrid->getProductGrid()->searchAndOpen($filter);
        if ($store) {
            $this->editProductPage->getFormPageActions()->changeStoreViewScope($store);
        }
        $this->editProductPage->getProductForm()->fill($product);
        $this->editProductPage->getFormPageActions()->save();

        return [
            'category' => $category,
            'stores' => isset($store) ? [$store] : [],
            'productNames' => isset($productName) ? $productName : [],
        ];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->configData) {
            $this->testStepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'rollback' => true]
            )->run();
        }
    }
}
