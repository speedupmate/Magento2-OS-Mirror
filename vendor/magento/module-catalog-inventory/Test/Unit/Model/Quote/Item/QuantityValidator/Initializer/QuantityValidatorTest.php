<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item as StockMock;
use Magento\Bundle\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\CatalogInventory\Helper\Data;
use Magento\Quote\Model\Quote\Item\Option as OptionItem;

/**
 * Quantity validation test
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuantityValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionInitializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parentItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parentStockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemInitializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockStatusInterface
     */
    private $stockStatusMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->stockRegistryMock = $this->getMock(
            StockRegistry::class,
            [],
            [],
            '',
            false
        );

        $this->stockStatusMock = $this->getMock(Status::class, [], [], '', false);

        $this->optionInitializer = $this->getMock(Option::class, [], [], '', false);
        $this->stockItemInitializer = $this->getMock(StockItem::class, [], [], '', false);
        $this->stockState = $this->getMock(StockState::class, [], [], '', false);
        $this->quantityValidator = $objectManagerHelper->getObject(
            QuantityValidator::class,
            [
                'optionInitializer' => $this->optionInitializer,
                'stockItemInitializer' => $this->stockItemInitializer,
                'stockRegistry' => $this->stockRegistryMock,
                'stockState' => $this->stockState
            ]
        );
        $this->observerMock = $this->getMock(Observer::class, [], [], '', false);
        $this->eventMock = $this->getMock(Event::class, ['getItem'], [], '', false);
        $this->quoteMock = $this->getMock(Quote::class, [], [], '', false);
        $this->storeMock = $this->getMock(Store::class, [], [], '', false);
        $this->quoteItemMock = $this->getMock(
            Item::class,
            ['getProductId', 'getQuote', 'getQty', 'getProduct', 'getParentItem',
                'addErrorInfo', 'setData', 'getQtyOptions'],
            [],
            '',
            false
        );
        $this->parentItemMock = $this->getMock(
            Item::class,
            ['getProduct', 'getId', 'getStore'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(Product::class, [], [], '', false);
        $this->stockItemMock = $this->getMock(StockMock::class, [], [], '', false);
        $this->parentStockItemMock = $this->getMock(StockMock::class, ['getStockStatus'], [], '', false);

        $this->typeInstanceMock = $this->getMock(Type::class, [], [], '', false);

        $this->resultMock = $this->getMock(
            DataObject::class,
            ['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'],
            [],
            '',
            false
        );
    }

    /**
     * This tests the scenario when item is not in stock
     *
     * @return void
     */
    public function testValidateOutOfStock()
    {
        $this->createInitialStub(0);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->stockRegistryMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);

        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                Data::ERROR_QTY,
                __('Some of the products are out of stock.')
            );
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when item is in stock but parent is not in stock
     *
     * @return void
     */
    public function testValidateInStock()
    {
        $this->createInitialStub(1);
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);

        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($this->parentItemMock);

        $this->stockRegistryMock->expects($this->at(2))
            ->method('getStockStatus')
            ->willReturn($this->parentStockItemMock);

        $this->parentStockItemMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(false);

        $this->stockStatusMock->expects($this->atLeastOnce())
            ->method('getStockStatus')
            ->willReturn(true);

        $this->quoteItemMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'cataloginventory',
                Data::ERROR_QTY,
                __('This product is out of stock.')
            );
        $this->quoteMock->expects($this->once())
            ->method('addErrorInfo')
            ->with(
                'stock',
                'cataloginventory',
                Data::ERROR_QTY,
                __('Some of the products are out of stock.')
            );
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario when item is in stock and has options
     *
     * @return void
     */
    public function testValidateWithOptions()
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError'])
            ->getMock();
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn($options);
        $this->optionInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
        $optionMock->expects($this->never())
            ->method('setHasError');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * This tests the scenario with options but has errors
     *
     * @return void
     */
    public function testValidateWithOptionsAndError()
    {
        $optionMock = $this->getMockBuilder(OptionItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHasError'])
            ->getMock();
        $this->stockRegistryMock->expects($this->at(0))
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->stockRegistryMock->expects($this->at(1))
            ->method('getStockStatus')
            ->willReturn($this->stockStatusMock);
        $options = [$optionMock];
        $this->createInitialStub(1);
        $this->setUpStubForQuantity(1, true);
        $this->setUpStubForRemoveError();
        $this->parentStockItemMock->expects($this->any())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->stockStatusMock->expects($this->once())
            ->method('getStockStatus')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->any())
            ->method('getQtyOptions')
            ->willReturn($options);
        $this->optionInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
        $optionMock->expects($this->never())
            ->method('setHasError');
        $this->quantityValidator->validate($this->observerMock);
    }

    /**
     * @param int $qty
     * @param bool $hasError
     * @return void
     */
    private function setUpStubForQuantity($qty, $hasError)
    {
        $this->productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstanceMock);
        $this->typeInstanceMock->expects($this->any())
            ->method('prepareQuoteItemQty')
            ->willReturn($qty);
        $this->quoteItemMock->expects($this->any())
            ->method('setData');
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->stockState->expects($this->any())
            ->method('checkQtyIncrements')
            ->willReturn($this->resultMock);
        $this->resultMock->expects($this->any())
            ->method('getHasError')
            ->willReturn($hasError);
        $this->resultMock->expects($this->any())
            ->method('getMessage')
            ->willReturn('');
        $this->resultMock->expects($this->any())
            ->method('getQuoteMessage')
            ->willReturn('');
        $this->resultMock->expects($this->any())
            ->method('getQuoteMessageIndex')
            ->willReturn('');
    }

    /**
     * @param int $qty
     * @return void
     */
    private function createInitialStub($qty)
    {
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->quoteMock->expects($this->any())
            ->method('getIsSuperMode')
            ->willReturn(0);
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->quoteItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);
        $this->quoteItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->eventMock->expects($this->any())
            ->method('getItem')
            ->willReturn($this->quoteItemMock);
        $this->observerMock->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->parentItemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->parentStockItemMock->expects($this->any())
            ->method('getIsInStock')
            ->willReturn(false);
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->quoteItemMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->any())
            ->method('addErrorInfo');
        $this->quoteMock->expects($this->any())
            ->method('addErrorInfo');
        $this->setUpStubForQuantity(0, false);
        $this->stockItemInitializer->expects($this->any())
            ->method('initialize')
            ->willReturn($this->resultMock);
    }

    /**
     * @return void
     */
    private function setUpStubForRemoveError()
    {
        $quoteItems = [$this->quoteItemMock];
        $this->quoteItemMock->expects($this->any())
            ->method('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->any())
            ->method('getItemsCollection')
            ->willReturn($quoteItems);
        $this->quoteMock->expects($this->any())
            ->method('getHasError')
            ->willReturn(false);
    }
}
