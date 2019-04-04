<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use \Magento\Catalog\Model\Product\Copier;

/**
 * Test class for \Magento\Catalog\Model\Product\Copier.
 */
class CopierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var Copier
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Product\CopyConstructorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $copyConstructorMock;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    protected function setUp()
    {
        $this->copyConstructorMock = $this->getMock(\Magento\Catalog\Model\Product\CopyConstructorInterface::class);
        $this->productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionRepositoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Option\Repository::class,
            [],
            [],
            '',
            false
        );
        $this->optionRepositoryMock;
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->productMock->expects($this->any())->method('getEntityId')->willReturn(1);

        $this->metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($this->metadata);
        $this->_model = new Copier(
            $this->copyConstructorMock,
            $this->productFactoryMock
        );

        $this->setProperties($this->_model, [
            'optionRepository' => $this->optionRepositoryMock,
            'metadataPool' => $metadataPool,
        ]);
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Copier::copy
     *
     * @return void
     */
    public function testCopy()
    {
        $this->productMock->expects($this->atLeastOnce())->method('getWebsiteIds');
        $this->productMock->expects($this->atLeastOnce())->method('getCategoryIds');
        $this->productMock->expects($this->any())->method('getData')->willReturnMap([
            ['', null, 'product data'],
            ['linkField', null, '1'],
        ]);

        $resourceMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Product::class, [], [], '', false);
        $this->productMock->expects($this->once())->method('getResource')->will($this->returnValue($resourceMock));

        $duplicateMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                '__wakeup',
                'setData',
                'setOptions',
                'getData',
                'setIsDuplicate',
                'setOriginalLinkId',
                'setStatus',
                'setCreatedAt',
                'setUpdatedAt',
                'setId',
                'setStoreId',
                'getEntityId',
                'save',
                'setUrlKey',
                'getUrlKey',
            ],
            [],
            '',
            false
        );
        $this->productFactoryMock->expects($this->once())->method('create')->will($this->returnValue($duplicateMock));

        $duplicateMock->expects($this->once())->method('setOptions')->with([]);
        $duplicateMock->expects($this->once())->method('setIsDuplicate')->with(true);
        $duplicateMock->expects($this->once())->method('setOriginalLinkId')->with(1);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        );
        $duplicateMock->expects($this->once())->method('setCreatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setUpdatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setId')->with(null);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStoreId'
        )->with(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        $duplicateMock->expects($this->once())->method('setData')->with('product data');
        $this->copyConstructorMock->expects($this->once())->method('build')->with($this->productMock, $duplicateMock);
        $duplicateMock->expects($this->once())->method('getUrlKey')->willReturn('urk-key-1');
        $duplicateMock->expects($this->once())->method('setUrlKey')->with('urk-key-2');
        $duplicateMock->expects($this->once())->method('save');

        $this->metadata->expects($this->any())->method('getLinkField')->willReturn('linkField');

        $duplicateMock->expects($this->any())->method('getData')->willReturnMap([
            ['linkField', null, '2'],
        ]);        $this->optionRepositoryMock->expects($this->once())
            ->method('duplicate')
            ->with($this->productMock, $duplicateMock);
        $resourceMock->expects($this->once())->method('duplicate')->with(1, 2);

        $this->assertEquals($duplicateMock, $this->_model->copy($this->productMock));
    }

    /**
     * Set object non-public properties.
     *
     * @param $object
     * @param array $properties
     * @return void
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
