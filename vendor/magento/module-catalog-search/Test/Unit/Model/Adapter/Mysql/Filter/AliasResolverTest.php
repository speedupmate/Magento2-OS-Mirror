<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Filter;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Tests \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver class.
 *
 */
class AliasResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver
     */
    private $aliasResolver;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->aliasResolver = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver::class,
            []
        );
    }

    /**
     * @param string $field
     * @param string $expectedAlias
     * @dataProvider aliasDataProvider
     */
    public function testGetFilterAlias($field, $expectedAlias)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->setMethods(['getField'])
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn($field);

        $this->assertSame($expectedAlias, $this->aliasResolver->getAlias($filter));
    }

    /**
     * @return array
     */
    public function aliasDataProvider()
    {
        return [
            'general' => [
                'field' => 'general',
                'alias' => 'general' . RequestGenerator::FILTER_SUFFIX,
            ],
            'price' => [
                'field' => 'price',
                'alias' => 'price_index',
            ],
            'category_ids' => [
                'field' => 'category_ids',
                'alias' => 'category_products_index',
            ],
            'visibility' => [
                'field' => 'visibility',
                'alias' => 'category_products_index',
            ],
        ];
    }
}
