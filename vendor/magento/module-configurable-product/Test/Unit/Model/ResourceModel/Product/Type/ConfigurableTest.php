<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relation;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    protected function setUp()
    {
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMock();
        $this->resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($connectionMock));
        $this->relation = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Relation::class,
            [],
            [],
            '',
            false
        );
        $this->metadataMock = $this->getMock(\Magento\Framework\EntityManager\EntityMetadata::class, [], [], '', false);
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->metadataMock);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurable = $this->objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class,
            [
                'resource' => $this->resource,
                'catalogProductRelation' => $this->relation,
            ]
        );
        $reflection = new \ReflectionClass(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        );
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->configurable, $this->metadataPoolMock);
    }
    public function testSaveProducts()
    {
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $mainProduct */
        $mainProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__sleep', '__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('link');
        $mainProduct->expects($this->once())
            ->method('getData')
            ->with('link')
            ->willReturn(3);
        $this->configurable->saveProducts($mainProduct, [1, 2, 3]);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetConfigurableOptions()
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Configurable|\PHPUnit_Framework_MockObject_MockObject $configurable */
        $configurable = $this->getMockBuilder(Configurable::class)
            ->setMethods(['getTable', 'getConnection'])
            ->setConstructorArgs([$context, $this->relation])
            ->getMock();
        $reflection = new \ReflectionClass(Configurable::class);
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($configurable, $this->metadataPoolMock);

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    '__sleep',
                    '__wakeup',
                    'getData',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('link');
        $product->expects($this->once())
            ->method('getData')
            ->with('link')
            ->willReturn('getId value');
        $configurable->expects($this->exactly(7))
            ->method('getTable')
            ->will(
                $this->returnValueMap(
                    [
                        ['catalog_product_super_attribute', 'catalog_product_super_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['catalog_product_super_link', 'catalog_product_super_link value'],
                        ['eav_attribute', 'eav_attribute value'],
                        ['catalog_product_entity', 'catalog_product_entity value'],
                        ['eav_attribute_option_value', 'eav_attribute_option_value value'],
                        ['catalog_product_super_attribute_label', 'catalog_product_super_attribute_label value']
                    ]
                )
            );
        $select = $this->getMock(
            \Magento\Framework\DB\Select::class,
            [
                'from',
                'joinInner',
                'joinLeft',
                'where',
            ],
            [],
            '',
            false
        );
        $select->expects($this->once())
            ->method('from')
            ->with(
                ['super_attribute' => 'catalog_product_super_attribute value'],
                [
                    'sku' => 'entity.sku',
                    'product_id' => 'product_entity.entity_id',
                    'attribute_code' => 'attribute.attribute_code',
                    'option_title' => 'option_value.value',
                    'super_attribute_label' =>  'attribute_label.value'
                ]
            )
            ->will($this->returnSelf());
        $superAttribute = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class,
            [
                'getBackendTable',
                'getAttributeId',
            ],
            [],
            '',
            false
        );
        $superAttribute->expects($this->once())
            ->method('getBackendTable')
            ->willReturn('getBackendTable value');
        $superAttribute->expects($this->once())
            ->method('getAttributeId')
            ->willReturn('getAttributeId value');
        $attributes = [
            $superAttribute,
        ];
        $select->expects($this->exactly(5))
            ->method('joinInner')
            ->will($this->returnSelf())
            ->withConsecutive(
                [
                    ['product_entity' => 'catalog_product_entity value'],
                    'product_entity.link = super_attribute.product_id',
                    []
                ],
                [
                    ['product_link' => 'catalog_product_super_link value'],
                    'product_link.parent_id = super_attribute.product_id',
                    []
                ],
                [
                    ['attribute' => 'eav_attribute value'],
                    'attribute.attribute_id = super_attribute.attribute_id',
                    []
                ],
                [
                    ['entity' => 'catalog_product_entity value'],
                    'entity.entity_id = product_link.product_id',
                    []
                ],
                [
                    ['entity_value' => 'getBackendTable value'],
                    implode(
                        ' AND ',
                        [
                            'entity_value.attribute_id = super_attribute.attribute_id',
                            'entity_value.store_id = 0',
                            'entity_value.link = entity.link'
                        ]
                    ),
                    []
                ]
            );
        $select->expects($this->exactly(2))
            ->method('joinLeft')
            ->will($this->returnSelf())
            ->withConsecutive(
                [
                    ['option_value' => 'eav_attribute_option_value value'],
                    implode(
                        ' AND ',
                        [
                            'option_value.option_id = entity_value.value',
                            'option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    ),
                    []
                ],
                [
                    ['attribute_label' => 'catalog_product_super_attribute_label value'],
                    implode(
                        ' AND ',
                        [
                            'super_attribute.product_super_attribute_id = attribute_label.product_super_attribute_id',
                            'attribute_label.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    ),
                    []
                ]
            );
        $select->expects($this->once())
            ->method('where')
            ->will($this->returnSelf())
            ->with(
                'super_attribute.product_id = ?',
                'getId value'
            );
        $readerAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods([
                'select',
                'fetchAll',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $readerAdapter->expects($this->once())
            ->method('select')
            ->willReturn($select);
        $readerAdapter->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn('fetchAll value');
        $configurable->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($readerAdapter);
        $expectedAttributesOptionsData = [
            'getAttributeId value' => 'fetchAll value',
        ];
        $actualAttributesOptionsData = $configurable->getConfigurableOptions($product, $attributes);
        $this->assertEquals($expectedAttributesOptionsData, $actualAttributesOptionsData);
    }
}
