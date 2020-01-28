<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order\ProductAttribute;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Setup\SetupData;

/**
 * Temando Order Item Interface Builder Test
 *
 * @package Temando\Shipping\Test\Unit
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderItemAttributeReaderTest extends TestCase
{
    /**
     * @var OrderItemAttributeReader
     */
    private $attributeReader;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->attributeReader = $objectManager->getObject(OrderItemAttributeReader::class);

        parent::setUp();
    }

    /**
     * Create the order item.
     *
     * @param Product $product
     * @return MockObject
     */
    private function createSimpleItemMock(Product $product): MockObject
    {
        $orderItem = $this->createMock(Item::class);
        $orderItem->method('getProductType')->willReturn(Type::TYPE_SIMPLE);
        $orderItem->method('getProduct')->willReturn($product);
        return $orderItem;
    }

    /**
     * Create a configurable item with different parent and child details.
     *
     * @param Product $parentProduct
     * @param Product $childProduct
     *
     * @return MockObject
     */
    private function createConfigItemMock(Product $parentProduct, Product $childProduct): MockObject
    {
        $simpleOption = $this->createMock(Item::class);
        $simpleOption->method('getProduct')->willReturn($childProduct);
        $orderItem = $this->createMock(Item::class);
        $orderItem->method('getProductType')->willReturn(Configurable::TYPE_CODE);
        $orderItem->method('getProduct')->willReturn($parentProduct);
        $orderItem->method('getChildrenItems')->willReturn([$simpleOption]);

        return $orderItem;
    }

    /**
     * Provide order items with expectations.
     *
     * (1) Simple order item with dimensions configured at the product.
     * (2) Simple order item with no dimensions configured at the product.
     * (3) Configurable order item with dimensions configured at the selected simple product.
     * (4) Configurable order item with no dimensions configured at the selected simple product.
     *
     * @return mixed[]
     */
    public function orderItemDataProvider()
    {
        $objectManager = new ObjectManager($this);

        // dimensions are configured at the simple product
        $dimensions = [
            SetupData::ATTRIBUTE_CODE_LENGTH => 3.0000,
            SetupData::ATTRIBUTE_CODE_WIDTH => 2.0000,
            SetupData::ATTRIBUTE_CODE_HEIGHT => 1.0000,
            SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE => PackagingType::PACKAGING_TYPE_PACKED,
            SetupData::ATTRIBUTE_CODE_PACKAGING_ID => (string) null
        ];

        // dimensions are not configured at the simple product
        $emptyDimensions = [
            SetupData::ATTRIBUTE_CODE_LENGTH => (float) null,
            SetupData::ATTRIBUTE_CODE_WIDTH => (float) null,
            SetupData::ATTRIBUTE_CODE_HEIGHT => (float) null,
            SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE => PackagingType::PACKAGING_TYPE_NONE,
            SetupData::ATTRIBUTE_CODE_PACKAGING_ID => (string) null
        ];

        /** @var Product $productNoDimensions */
        $productNoDimensions = $objectManager->getObject(Product::class, ['entity_id' => 123]);

        /** @var Product $productWithDimensions */
        $productWithDimensions = $objectManager->getObject(Product::class, ['entity_id' => 456]);
        $productWithDimensions->addData($dimensions);

        // (1) simple item, no dimensions configured
        $itemNoDimensions = $this->createSimpleItemMock($productNoDimensions);

        // (2) configurable item, no dimensions configured
        $configItemNoDimensions = $this->createConfigItemMock($productNoDimensions, $productNoDimensions);

        // (3) simple item, dimensions configured
        $itemWithDimensions = $this->createSimpleItemMock($productWithDimensions);

        // (4) configurable item, dimensions configured
        $configItemWithDimensions = $this->createConfigItemMock($productNoDimensions, $productWithDimensions);

        return [
            'simpleWithDimensions' => [$itemWithDimensions, $dimensions],
            'simpleNoDimensions' => [$itemNoDimensions, $emptyDimensions],
            'configurableWithDimensions' => [$configItemWithDimensions, $dimensions],
            'configurableNoDimensions' => [$configItemNoDimensions, $emptyDimensions]
        ];
    }

    /**
     * @test
     * @dataProvider orderItemDataProvider
     *
     * @param Item $item
     * @param float[] $expectedValues
     */
    public function readDimensions(Item $item, array $expectedValues)
    {
        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_LENGTH],
            $this->attributeReader->getLength($item)
        );

        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_WIDTH],
            $this->attributeReader->getWidth($item)
        );

        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_HEIGHT],
            $this->attributeReader->getHeight($item)
        );
    }

    /**
     * Provide the readPackagingDetails test with quote Items
     *
     * (1) Configurable Parent with PrePacked details and a child with no details
     * (2) Configurable Parent with Assigned details and a child with no details
     * (3) Configurable Parent with PrePacked details and a child with Assigned details
     * (4) Configurable Parent with no details and a child with no details
     *
     * @return mixed[]
     */
    public function orderItemWithPackagingDetailsDataProvider()
    {
        $objectManager = new ObjectManager($this);

        $packagingTypePacked = [
            Product::WEIGHT => 10.00,
            SetupData::ATTRIBUTE_CODE_LENGTH => 3.0000,
            SetupData::ATTRIBUTE_CODE_WIDTH => 2.0000,
            SetupData::ATTRIBUTE_CODE_HEIGHT => 1.0000,
            SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE => PackagingType::PACKAGING_TYPE_PACKED,
            SetupData::ATTRIBUTE_CODE_PACKAGING_ID => (string) null
        ];

        $packagingTypeAssigned = [
            Product::WEIGHT => (float) null,
            SetupData::ATTRIBUTE_CODE_LENGTH => (float) null,
            SetupData::ATTRIBUTE_CODE_WIDTH => (float) null,
            SetupData::ATTRIBUTE_CODE_HEIGHT => (float) null,
            SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE => PackagingType::PACKAGING_TYPE_ASSIGNED,
            SetupData::ATTRIBUTE_CODE_PACKAGING_ID => '1111-1111-1111-1111'
        ];

        $packagingTypeNone = [
            Product::WEIGHT => (float) null,
            SetupData::ATTRIBUTE_CODE_LENGTH => (float) null,
            SetupData::ATTRIBUTE_CODE_WIDTH => (float) null,
            SetupData::ATTRIBUTE_CODE_HEIGHT => (float) null,
            SetupData::ATTRIBUTE_CODE_PACKAGING_TYPE => PackagingType::PACKAGING_TYPE_NONE,
            SetupData::ATTRIBUTE_CODE_PACKAGING_ID => (string) null
        ];

        /** @var Product $productTypePacked */
        $productTypePacked = $objectManager->getObject(Product::class, ['entity_id' => 1001]);
        $productTypePacked->addData($packagingTypePacked);

        /** @var Product $productTypeAssigned */
        $productTypeAssigned = $objectManager->getObject(Product::class, ['entity_id' => 1002]);
        $productTypeAssigned->addData($packagingTypeAssigned);

        /** @var Product $productTypeNone */
        $productTypeNone = $objectManager->getObject(Product::class, ['entity_id' => 1003]);
        $productTypeNone->addData($packagingTypeNone);

        // (1) Configurable Parent with PrePacked details and a child with no details
        $configParentPackedChildNone = $this->createConfigItemMock($productTypePacked, $productTypeNone);
        $configParentPackedChildNoneExpected = array_merge(
            $packagingTypePacked,
            [
                'isPrePackaged' => true
            ]
        );

        // (2) Configurable Parent with Assigned details and a child with no details
        $configParentAssignedChildNone = $this->createConfigItemMock($productTypeAssigned, $productTypeNone);
        $configParentAssignedChildNoneExpected = array_merge(
            $packagingTypeAssigned,
            [
                'isPrePackaged' => true
            ]
        );

        // (3) Configurable Parent with PrePacked details and a child with Assigned details
        $configParentPackedChildAssigned = $this->createConfigItemMock($productTypePacked, $productTypeAssigned);
        $configParentPackedChildAssignedExpected = array_merge(
            $packagingTypeAssigned,
            [
                'isPrePackaged' => true
            ]
        );

        // (4) Configurable Parent with no details and a child with no details
        $configParentNoneChildNone = $this->createConfigItemMock($productTypeNone, $productTypeNone);
        $configParentNoneChildNoneExpected = array_merge(
            $packagingTypeNone,
            [
                'isPrePackaged' => false
            ]
        );

        return [
            'childInheritsParentPacked' => [$configParentPackedChildNoneExpected, $configParentPackedChildNone],
            'childInheritsParentAssigned' => [$configParentAssignedChildNoneExpected, $configParentAssignedChildNone],
            'childDoesNotInheritParent' => [$configParentPackedChildAssignedExpected, $configParentPackedChildAssigned],
            'noPackageDetails' => [$configParentNoneChildNoneExpected, $configParentNoneChildNone]
        ];
    }

    /**
     * @test
     * @dataProvider orderItemWithPackagingDetailsDataProvider
     *
     * @param array $expectedValues
     * @param Item $quoteItem
     */
    public function readPackagingDetails(array $expectedValues, Item $quoteItem)
    {
        self::assertEquals(
            $expectedValues[SetupData::ATTRIBUTE_CODE_PACKAGING_ID],
            $this->attributeReader->getPackageId($quoteItem)
        );

        self::assertEquals(
            $expectedValues['isPrePackaged'],
            $this->attributeReader->isPrePackaged($quoteItem)
        );

        self::assertEquals(
            $expectedValues[SetupData::ATTRIBUTE_CODE_LENGTH],
            $this->attributeReader->getLength($quoteItem)
        );

        self::assertEquals(
            $expectedValues[SetupData::ATTRIBUTE_CODE_WIDTH],
            $this->attributeReader->getWidth($quoteItem)
        );

        self::assertEquals(
            $expectedValues[SetupData::ATTRIBUTE_CODE_HEIGHT],
            $this->attributeReader->getHeight($quoteItem)
        );
    }
}
