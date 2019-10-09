<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Order\ProductAttribute;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Temando\Shipping\Model\Source\PackagingType;
use Temando\Shipping\Setup\SetupData;

/**
 * Temando Quote Item Attribute Reader Test
 *
 * @package Temando\Shipping\Test\Unit
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QuoteItemAttributeReaderTest extends TestCase
{
    /**
     * @var QuoteItemAttributeReader
     */
    private $attributeReader;

    /**
     * Init test subject
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->attributeReader = $objectManager->getObject(QuoteItemAttributeReader::class);

        parent::setUp();
    }

    /**
     * Create a simple item mock.
     *
     * @param Product $product
     * @return MockObject
     */
    private function createSimpleItemMock(Product $product): MockObject
    {
        $quoteItem = $this->createMock(Item::class);
        $quoteItem->method('getOptionByCode')->with('simple_product')->willReturn(null);
        $quoteItem->method('getProduct')->willReturn($product);

        return $quoteItem;
    }

    /**
     * Create a configurable item with different parent and child details.
     *
     * @param Product $parentProduct
     * @param Option $childOption
     *
     * @return MockObject
     */
    private function createConfigParentChildItemMock(Product $parentProduct, Option $childOption): MockObject
    {
        $quoteItem = $this->createMock(Item::class);
        $quoteItem->method('getOptionByCode')->with('simple_product')->willReturn($childOption);
        $quoteItem->method('getProduct')->willReturn($parentProduct);

        return $quoteItem;
    }

    /**
     * Provide quote items with expectations.
     *
     * (1) Simple quote item with dimensions configured at the product.
     * (2) Simple quote item with no dimensions configured at the product.
     * (3) Configurable quote item with dimensions configured at the selected simple product.
     * (4) Configurable quote item with no dimensions configured at the selected simple product.
     *
     * @return mixed[]
     */
    public function quoteItemDataProvider()
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
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productNoDimensions);
        $configItemNoDimensions = $this->createConfigParentChildItemMock($productNoDimensions, $simpleOption);

        // (3) simple item, dimensions configured
        $itemWithDimensions = $this->createSimpleItemMock($productWithDimensions);

        // (4) configurable item, dimensions configured
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productWithDimensions);
        $configItemWithDimensions = $this->createConfigParentChildItemMock($productNoDimensions, $simpleOption);

        return [
            'simpleWithDimensions' => [$itemWithDimensions, $dimensions],
            'simpleNoDimensions' => [$itemNoDimensions, $emptyDimensions],
            'configurableWithDimensions' => [$configItemWithDimensions, $dimensions],
            'configurableNoDimensions' => [$configItemNoDimensions, $emptyDimensions]
        ];
    }

    /**
     * @test
     * @dataProvider quoteItemDataProvider
     *
     * @param Item $quoteItem
     * @param float[] $expectedValues
     */
    public function readDimensions(Item $quoteItem, array $expectedValues)
    {
        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_LENGTH],
            $this->attributeReader->getLength($quoteItem)
        );

        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_WIDTH],
            $this->attributeReader->getWidth($quoteItem)
        );

        self::assertSame(
            $expectedValues[SetupData::ATTRIBUTE_CODE_HEIGHT],
            $this->attributeReader->getHeight($quoteItem)
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
    public function quoteItemWithPackagingDetailsDataProvider()
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
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productTypeNone);
        $configParentPackedChildNone = $this->createConfigParentChildItemMock($productTypePacked, $simpleOption);
        $configParentPackedChildNoneExpected = array_merge(
            $packagingTypePacked,
            [
                'isPrePackaged' => true
            ]
        );

        // (2) Configurable Parent with Assigned details and a child with no details
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productTypeNone);
        $configParentAssignedChildNone = $this->createConfigParentChildItemMock($productTypeAssigned, $simpleOption);
        $configParentAssignedChildNoneExpected = array_merge(
            $packagingTypeAssigned,
            [
                'isPrePackaged' => true
            ]
        );

        // (3) Configurable Parent with PrePacked details and a child with Assigned details
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productTypeAssigned);
        $configParentPackedChildAssigned = $this->createConfigParentChildItemMock($productTypePacked, $simpleOption);
        $configParentPackedChildAssignedExpected = array_merge(
            $packagingTypeAssigned,
            [
                'isPrePackaged' => true
            ]
        );

        // (4) Configurable Parent with no details and a child with no details
        /** @var Option $simpleOption */
        $simpleOption = $objectManager->getObject(Option::class);
        $simpleOption->setProduct($productTypeNone);
        $configParentNoneChildNone = $this->createConfigParentChildItemMock($productTypeNone, $simpleOption);
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
     * @dataProvider quoteItemWithPackagingDetailsDataProvider
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
