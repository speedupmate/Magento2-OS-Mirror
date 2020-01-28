<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Controller\Adminhtml\Configuration\Packaging;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Temando\Shipping\Test\Integration\Fixture\ApiTokenFixture;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture createApiToken
 */
class EditTest extends AbstractBackendController
{
    /**
     * The resource used to authorize action
     *
     * @var string
     */
    protected $resource = 'Temando_Shipping::packaging';

    /**
     * The uri at which to access the controller
     *
     * @var string
     */
    protected $uri = 'backend/temando/configuration_packaging/edit';

    /**
     * delegate fixtures creation to separate class.
     */
    public static function createApiToken()
    {
        ApiTokenFixture::createValidToken();
    }

    /**
     * delegate fixtures rollback to separate class.
     */
    public static function createApiTokenRollback()
    {
        ApiTokenFixture::rollbackToken();
    }

    /**
     * @test
     *
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function pageIsRendered()
    {
        $containerId = '1234-abcd';
        $this->getRequest()->setParam('packaging_id', $containerId);
        $this->dispatch($this->uri);

        $this->assertContains('ContainerForm', $this->getResponse()->getBody());
        $this->assertContains('"containerId": "' . $containerId . '"', $this->getResponse()->getBody());
    }

    /**
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function testAclHasAccess()
    {
        parent::testAclHasAccess();
    }

    /**
     * @magentoConfigFixture default/carriers/temando/account_id 23
     * @magentoConfigFixture default/carriers/temando/bearer_token 808
     */
    public function testAclNoAccess()
    {
        parent::testAclNoAccess();
    }
}
