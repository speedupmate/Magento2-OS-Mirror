<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Attribute\Mapping\Product;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Temando\Shipping\Model\ResourceModel\Attribute\Mapping\ProductRepository;

/**
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
    /**
     * The resource used to authorize action
     *
     * @var string
     */
    protected $resource = 'Temando_Shipping::product';

    /**
     * The uri at which to access the controller
     *
     * @var string
     */
    protected $uri = 'backend/temando/attribute_mapping_product/save/';

    /**
     * Save failed. Assert messages being collected.
     *
     * @test
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function saveFailure()
    {
        $mappings = [
            'product.classificationCodes.hsCode' => [
                'mapped_attribute' => 'ts_hs_code'
            ]
        ];

        $errorText = 'Unable to create attribute mapping.';

        $repositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('save')
            ->willThrowException(new \Magento\Framework\Exception\CouldNotSaveException(__($errorText)));

        Bootstrap::getObjectManager()->addSharedInstance($repositoryMock, ProductRepository::class);

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
            'mapping' => $mappings
        ]);

        $this->dispatch($this->uri);

        $this->assertTrue($this->getResponse()->isRedirect());

        /** @var MessageManager $messageManager */
        $messageManager = Bootstrap::getObjectManager()->get(MessageManager::class);
        $errors = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_ERROR);
        $warnings = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_WARNING);
        $success = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_SUCCESS);
        $this->assertCount(1, $errors);
        $this->assertCount(0, $warnings);
        $this->assertCount(0, $success);
    }

    /**
     * Save success. Assert messages being collected.
     *
     * @test
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function saveSuccess()
    {
        $mappings = [
            'product.classificationCodes.hsCode' => [
                'mapped_attribute' => 'ts_hs_code'
            ]
        ];

        $repositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('save');

        Bootstrap::getObjectManager()->addSharedInstance($repositoryMock, ProductRepository::class);

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
            'mapping' => $mappings
        ]);

        $this->dispatch($this->uri);

        $this->assertTrue($this->getResponse()->isRedirect());

        /** @var MessageManager $messageManager */
        $messageManager = Bootstrap::getObjectManager()->get(MessageManager::class);
        $errors = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_ERROR);
        $warnings = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_WARNING);
        $success = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_SUCCESS);
        $this->assertCount(0, $errors);
        $this->assertCount(0, $warnings);
        $this->assertCount(1, $success);
    }

    /**
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function testAclHasAccess()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
            'mapping' => [
                'product.classificationCodes.hsCode' => [
                    'mapped_attribute' => 'ts_hs_code'
                ]
            ],
        ]);

        parent::testAclHasAccess();
    }

    /**
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function testAclNoAccess()
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
            'mapping' => [
                'product.classificationCodes.hsCode' => [
                    'mapped_attribute' => 'ts_hs_code'
                ]
            ],
        ]);

        parent::testAclNoAccess();
    }
}
