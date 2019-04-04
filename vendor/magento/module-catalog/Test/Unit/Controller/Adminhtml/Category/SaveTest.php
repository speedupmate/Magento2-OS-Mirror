<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Category\Save;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * Class SaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $titleMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * Config mock holder.
     *
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavCongig;

    /**
     * StoreManager mock holder.
     *
     * @var StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Save
     */
    private $save;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getTitle',
                'getRequest',
                'getObjectManager',
                'getEventManager',
                'getResponse',
                'getMessageManager',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );
        $this->resultRedirectFactoryMock = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRawFactoryMock = $this->getMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            [],
            [],
            '',
            false
        );
        $this->resultJsonFactoryMock = $this->getMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->layoutFactoryMock = $this->getMock(
            \Magento\Framework\View\LayoutFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost', 'getPostValue']
        );
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->responseMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false
        );
        $this->messageManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['addSuccess', 'getMessages']
        );

        $this->contextMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->save = $this->objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock,
                'storeManager' => $this->storeManager
            ]
        );
        $this->eavCongig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->setBackwardCompatibleProperty($this->save, 'eavConfig', $this->eavCongig);
    }

    /**
     * Run test execute method
     *
     * @param int|bool $categoryId
     * @param int $storeId
     * @param int|null $parentId
     * @return void
     *
     * @dataProvider dataProviderExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($categoryId, $storeId, $parentId)
    {
        $rootCategoryId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $products = [['any_product']];
        $postData = [
            'general-data',
            'parent' => $parentId,
            'category_products' => json_encode($products),
        ];

        if (isset($storeId)) {
            $postData['store_id'] = $storeId;
        }
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect
         * |\PHPUnit_Framework_MockObject_MockObject $resultRedirectMock
         */
        $resultRedirectMock = $this->getMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            [],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\View\Element\Messages
         * |\PHPUnit_Framework_MockObject_MockObject $blockMock
         */
        $blockMock = $this->getMock(
            \Magento\Framework\View\Element\Messages::class,
            ['setMessages', 'getGroupedHtml'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit_Framework_MockObject_MockObject $categoryMock
         */
        $categoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'setStoreId',
                'load',
                'getPath',
                'getResource',
                'setPath',
                'setParentId',
                'setData',
                'addData',
                'setAttributeSetId',
                'getDefaultAttributeSetId',
                'getProductsReadonly',
                'setPostedProducts',
                'getId',
                'validate',
                'unsetData',
                'save',
                'toArray'
            ],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Catalog\Model\Category
         * |\PHPUnit_Framework_MockObject_MockObject $parentCategoryMock
         */
        $parentCategoryMock = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'setStoreId',
                'load',
                'getPath',
                'setPath',
                'setParentId',
                'setData',
                'addData',
                'setAttributeSetId',
                'getDefaultAttributeSetId',
                'getProductsReadonly',
                'setPostedProducts',
                'getId'
            ],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Backend\Model\Auth\Session
         * |\PHPUnit_Framework_MockObject_MockObject $sessionMock
         */
        $sessionMock = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            [],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\Registry
         * |\PHPUnit_Framework_MockObject_MockObject $registryMock
         */
        $registryMock = $this->getMock(
            \Magento\Framework\Registry::class,
            ['register'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Cms\Model\Wysiwyg\Config
         * |\PHPUnit_Framework_MockObject_MockObject $wysiwygConfigMock
         */
        $wysiwygConfigMock = $this->getMock(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            ['setStoreId'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         * |\PHPUnit_Framework_MockObject_MockObject $storeManagerMock
         */
        $storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getRootCategoryId']
        );
        /**
         * @var \Magento\Framework\View\Layout
         * |\PHPUnit_Framework_MockObject_MockObject $layoutMock
         */
        $layoutMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Layout::class,
            [],
            '',
            false,
            true,
            true,
            ['getMessagesBlock']
        );
        /**
         * @var \Magento\Framework\Controller\Result\Json
         * |\PHPUnit_Framework_MockObject_MockObject $resultJsonMock
         */
        $resultJsonMock = $this->getMock(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            ['setData'],
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\Message\Collection
         * |\PHPUnit_Framework_MockObject_MockObject $messagesMock
         */
        $messagesMock = $this->getMock(
            \Magento\Framework\Message\Collection::class,
            [],
            [],
            '',
            false
        );

        $messagesMock->expects($this->once())
            ->method('getCountByType')
            ->will($this->returnValue(0));

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirectMock));
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', false, $categoryId],
                        ['store', null, $storeId],
                        ['parent', null, $parentId],
                    ]
                )
            );
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($categoryMock));
        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [\Magento\Backend\Model\Auth\Session::class, $sessionMock],
                        [\Magento\Framework\Registry::class, $registryMock],
                        [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwygConfigMock],
                        [\Magento\Store\Model\StoreManagerInterface::class, $storeManagerMock],
                    ]
                )
            );
        $categoryMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $registryMock->expects($this->any())
            ->method('register')
            ->will(
                $this->returnValueMap(
                    [
                        ['category', $categoryMock],
                        ['current_category', $categoryMock],
                    ]
                )
            );
        $wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->will(
                $this->returnValueMap(
                    [
                        ['use_config', ['attribute']],
                        ['use_default', ['default-attribute']],
                        ['return_session_messages_only', true],
                    ]
                )
            );
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturn($postData);
        $addData = $postData;
        $categoryMock->expects($this->once())
            ->method('addData')
            ->with($addData);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($categoryId));
        if (!$parentId) {
            if ($storeId) {
                $storeManagerMock->expects($this->once())
                    ->method('getStore')
                    ->with($storeId)
                    ->will($this->returnSelf());
                $storeManagerMock->expects($this->once())
                    ->method('getRootCategoryId')
                    ->will($this->returnValue($rootCategoryId));
                $parentId = $rootCategoryId;
            }
        }
        $categoryMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($parentCategoryMock));
        $parentCategoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('parent_category_path'));
        $parentCategoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($parentId));
        $categoryMock->expects($this->once())
            ->method('setPath')
            ->with('parent_category_path');
        $categoryMock->expects($this->once())
            ->method('setParentId')
            ->with($parentId);
        $categoryMock->expects($this->atLeastOnce())
            ->method('setData')
            ->will(
                $this->returnValueMap(
                    [
                        ['attribute', null, true],
                        ['default-attribute', false, true],
                        ['use_post_data_config', ['attribute'], true],
                    ]
                )
            );
        $categoryMock->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->will($this->returnValue('default-attribute'));
        $categoryMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('default-attribute');
        $categoryMock->expects($this->once())
            ->method('getProductsReadonly')
            ->will($this->returnValue(false));
        $categoryMock->expects($this->once())
            ->method('setPostedProducts')
            ->with($products);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'catalog_category_prepare_save',
                ['category' => $categoryMock, 'request' => $this->requestMock]
            );

        $categoryResource = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category::class,
            [],
            [],
            '',
            false
        );
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($categoryResource));
        $categoryMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $categoryMock->expects($this->once())
            ->method('unsetData')
            ->with('use_post_data_config');
        $categoryMock->expects($this->once())
            ->method('save');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('You saved the category.'));
        $categoryMock->expects($this->at(1))
            ->method('getId')
            ->will($this->returnValue(111));
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->will($this->returnValue($blockMock));
        $this->messageManagerMock->expects($this->any())
            ->method('getMessages')
            ->will($this->returnValue($messagesMock));
        $blockMock->expects($this->once())
            ->method('setMessages')
            ->with($messagesMock);
        $blockMock->expects($this->once())
            ->method('getGroupedHtml')
            ->will($this->returnValue('grouped-html'));
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([]);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultJsonMock));
        $this->eavCongig->expects($this->once())
            ->method('getEntityType')
            ->with(CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn($entityType);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getCode')
            ->willReturn('testCode');
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $categoryMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue(['category-data']));
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => 'grouped-html',
                    'error' => false,
                    'category' => ['category-data'],
                ]
            )
            ->will($this->returnValue('result-execute'));

        $this->assertEquals('result-execute', $this->save->execute());
    }

    /**
     * Data provider for execute
     *
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => 123,
            ],
            [
                'categoryId' => false,
                'storeId' => 7,
                'parentId' => null,
            ]
        ];
    }

    /**
     * Test Save::ImagePreprocessing() does set image attribute data to false if there are no value(image was removed).
     *
     * @dataProvider imagePreprocessingDataProvider
     * @param array $data
     * @return void
     */
    public function testImagePreprocessingWithoutValue($data)
    {
        $eavConfig = $this->getMock(\Magento\Eav\Model\Config::class, ['getEntityType'], [], '', false);
        $imageBackendModel = $this->objectManager->getObject(
            \Magento\Catalog\Model\Category\Attribute\Backend\Image::class
        );
        $collection = new \Magento\Framework\DataObject([
            'attribute_collection' => [
                new \Magento\Framework\DataObject([
                    'attribute_code' => 'attribute1',
                    'backend' => $imageBackendModel
                ]),
                new \Magento\Framework\DataObject([
                    'attribute_code' => 'attribute2',
                    'backend' => new \Magento\Framework\DataObject()
                ])
            ]
        ]);
        $eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->will($this->returnValue($collection));
        $model = $this->objectManager->getObject(Save::class, [
            'eavConfig' => $eavConfig
        ]);
        $result = $model->imagePreprocessing($data);
        $this->assertEquals([
            'attribute1' => false,
            'attribute2' => 123
        ], $result);
    }

    /**
     * Test Save::ImagePreprocessing() doesn't set image attribute data to false if image wasn't removed(value exists).
     *
     * @return void
     */
    public function testImagePreprocessingWithValue()
    {
        $eavConfig = $this->getMock(\Magento\Eav\Model\Config::class, ['getEntityType'], [], '', false);
        $imageBackendModel = $this->objectManager->getObject(
            \Magento\Catalog\Model\Category\Attribute\Backend\Image::class
        );
        $collection = new \Magento\Framework\DataObject([
            'attribute_collection' => [
                new \Magento\Framework\DataObject([
                    'attribute_code' => 'attribute1',
                    'backend' => $imageBackendModel
                ]),
                new \Magento\Framework\DataObject([
                    'attribute_code' => 'attribute2',
                    'backend' => new \Magento\Framework\DataObject()
                ])
            ]
        ]);
        $eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->will($this->returnValue($collection));
        $model = $this->objectManager->getObject(Save::class, [
            'eavConfig' => $eavConfig
        ]);
        $result = $model->imagePreprocessing([
            'attribute1' => 'somevalue',
            'attribute2' => null
        ]);
        $this->assertEquals([
            'attribute1' => 'somevalue',
            'attribute2' => null
        ], $result);
    }

    /**
     * Test data for testImagePreprocessingWithoutValue.
     *
     * @return array
     */
    public function imagePreprocessingDataProvider()
    {
        return [
            [['attribute1' => null, 'attribute2' => 123]],
            [['attribute2' => 123]]
        ];
    }
}
