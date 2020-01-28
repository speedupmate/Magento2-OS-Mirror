<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Temando Shipping Panel Modifier Test
 *
 * @magentoAppArea adminhtml
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShippingPanelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingPanel
     */
    private $modifier;

    /**
     * Prepare modifier (test subject).
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $store = $objectManager->get(StoreInterface::class);
        $locatorMock = $this->createMock(LocatorInterface::class);
        $locatorMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->modifier = Bootstrap::getObjectManager()->create(ShippingPanel::class, [
            'locator' => $locatorMock,
        ]);
    }

    /**
     * Provide reduced product form meta data.
     *
     * @return mixed[]
     */
    public function metaProvider()
    {
        $skuField['arguments']['data']['config'] = [
            'dataType' => 'text',
            'formElement' => 'input',
            'visible' => '1',
            'required' => '1',
            'code' => 'sku',
            'source' => 'product-details',
            'globalScope' => true,
            'sortOrder' => 30,
            'componentType' => 'field',
        ];
        $skuGroup['children']['sku'] = $skuField;

        $lengthField['arguments']['data']['config'] = [
            'dataType' => 'text',
            'formElement' => 'input',
            'visible' => '1',
            'required' => '0',
            'code' => 'ts_dimensions_length',
            'source' => 'product-details',
            'globalScope' => false,
            'sortOrder' => 70,
            'componentType' => 'field',
        ];
        $lengthGroup['children']['ts_dimensions_length'] = $lengthField;

        $packagingTypeField['arguments']['data']['config'] = [
            'dataType' => 'select',
            'formElement' => 'select',
            'visible' => '1',
            'required' => '0',
            'code' => 'ts_packaging_type',
            'source' => 'product-details',
            'globalScope' => true,
            'sortOrder' => 110,
            'options' => [
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'packed', 'label' => 'Pre-packaged'],
                ['value' => 'assigned', 'label' => 'Assigned'],
            ],
            'componentType' => 'field',
        ];
        $packagingTypeGroup['children']['ts_packaging_type'] = $packagingTypeField;

        $packagingIdField['arguments']['data']['config'] = [
            'dataType' => 'select',
            'formElement' => 'select',
            'visible' => '1',
            'required' => '0',
            'code' => 'ts_packaging_id',
            'source' => 'product-details',
            'globalScope' => true,
            'sortOrder' => 120,
            'options' => [
                ['value' => 'abc', 'label' => 'Test Package 1'],
                ['value' => 'def', 'label' => 'Test Package 2'],
                ['value' => 'ghi', 'label' => 'Test Package 3'],
            ],
            'componentType' => 'field',
        ];
        $packagingIdGroup['children']['ts_packaging_id'] = $packagingIdField;

        $meta[ShippingPanel::DEFAULT_GENERAL_PANEL]['children']['container_sku'] = $skuGroup;
        $meta[ShippingPanel::DEFAULT_GENERAL_PANEL]['children']['container_ts_dimensions_length'] = $lengthGroup;
        $meta[ShippingPanel::DEFAULT_GENERAL_PANEL]['children']['container_ts_packaging_type'] = $packagingTypeGroup;
        $meta[ShippingPanel::DEFAULT_GENERAL_PANEL]['children']['container_ts_packaging_id'] = $packagingIdGroup;

        return [[$meta]];
    }

    /**
     * Assert that shipping panel is added to metadata.
     *
     * @test
     * @dataProvider metaProvider
     *
     * @param mixed[] $meta
     */
    public function shippingPanelIsCreated(array $meta)
    {
        self::assertCount(1, $meta);
        self::assertArrayHasKey(ShippingPanel::DEFAULT_GENERAL_PANEL, $meta);
        self::assertArrayNotHasKey(ShippingPanel::SHIPPING_PANEL, $meta);

        $meta = $this->modifier->modifyMeta($meta);

        self::assertCount(2, $meta);
        self::assertArrayHasKey(ShippingPanel::DEFAULT_GENERAL_PANEL, $meta);
        self::assertArrayHasKey(ShippingPanel::SHIPPING_PANEL, $meta);
    }

    /**
     * Assert all `ts_` attributes are moved to shipping panel.
     *
     * @test
     * @dataProvider metaProvider
     * @magentoAdminConfigFixture general/locale/weight_unit kgs
     * @magentoConfigFixture default_store general/locale/weight_unit kgs
     *
     * @param mixed[] $meta
     */
    public function shippingAttributesAreMoved(array $meta)
    {
        $meta = $this->modifier->modifyMeta($meta);

        $defaultChildren = $meta[ShippingPanel::DEFAULT_GENERAL_PANEL]['children'];
        $shippingChildren = $meta[ShippingPanel::SHIPPING_PANEL]['children'];

        foreach ($defaultChildren as $key => $defaultChild) {
            self::assertTrue(strpos($key, 'ts_') === false, 'Shipping attribute left in default panel.');
        }

        foreach ($shippingChildren as $key => $shippingChild) {
            self::assertTrue(strpos($key, 'ts_') >= 0, 'Unexpected attribute moved to shipping panel.');
        }
    }

    /**
     * Assert that unit of measure is added to dimensions fields.
     *
     * @test
     * @dataProvider metaProvider
     * @magentoAdminConfigFixture general/locale/weight_unit kgs
     * @magentoConfigFixture default_store general/locale/weight_unit kgs
     *
     * @param mixed[] $meta
     */
    public function uomIsAdded(array $meta)
    {
        $meta = $this->modifier->modifyMeta($meta);

        $lengthContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_dimensions_length'];
        $lengthMeta = $lengthContainerMeta['children']['ts_dimensions_length']['arguments']['data']['config'];
        self::assertArrayHasKey('addafter', $lengthMeta);
        self::assertArrayHasKey('additionalClasses', $lengthMeta);

        self::assertEquals('cm', $lengthMeta['addafter']);
        self::assertEquals('admin__field-small', $lengthMeta['additionalClasses']);
    }

    /**
     * Assert that packaging attributes are visible when account is registered.
     *
     * @test
     * @dataProvider metaProvider
     * @magentoAdminConfigFixture carriers/temando/account_id accountId
     * @magentoAdminConfigFixture carriers/temando/bearer_token bearerToken
     *
     * @param mixed[] $meta
     */
    public function packagingAttributesAreVisible(array $meta)
    {
        $meta = $this->modifier->modifyMeta($meta);

        $lengthContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_dimensions_length'];
        $lengthMeta = $lengthContainerMeta['children']['ts_dimensions_length']['arguments']['data']['config'];
        self::assertTrue($lengthMeta['visible']);

        $packagingContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_packaging_type'];
        $packagingMeta = $packagingContainerMeta['children']['ts_packaging_type']['arguments']['data']['config'];
        self::assertTrue($packagingMeta['visible']);

        $packagingIdContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_packaging_id'];
        $packagingIdMeta = $packagingIdContainerMeta['children']['ts_packaging_id']['arguments']['data']['config'];
        self::assertTrue($packagingIdMeta['visible']);
    }

    /**
     * Assert that packaging attributes are not visible when account is not registered.
     *
     * @test
     * @dataProvider metaProvider
     *
     * @param mixed[] $meta
     */
    public function packagingAttributesAreNotVisible(array $meta)
    {
        $meta = $this->modifier->modifyMeta($meta);

        $lengthContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_dimensions_length'];
        $lengthMeta = $lengthContainerMeta['children']['ts_dimensions_length']['arguments']['data']['config'];
        self::assertTrue($lengthMeta['visible']);

        $packagingContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_packaging_type'];
        $packagingMeta = $packagingContainerMeta['children']['ts_packaging_type']['arguments']['data']['config'];
        self::assertFalse($packagingMeta['visible']);

        $packagingIdContainerMeta = $meta[ShippingPanel::SHIPPING_PANEL]['children']['container_ts_packaging_id'];
        $packagingIdMeta = $packagingIdContainerMeta['children']['ts_packaging_id']['arguments']['data']['config'];
        self::assertFalse($packagingIdMeta['visible']);
    }
}
