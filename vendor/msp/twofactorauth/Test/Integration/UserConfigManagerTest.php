<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace MSP\TwoFactorAuth\Test\Integration;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User\Collection as AdminUserCollection;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class UserConfigManagerTest extends TestCase
{
    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->userConfigManager = Bootstrap::getObjectManager()->get(UserConfigManagerInterface::class);
        $this->serializer = Bootstrap::getObjectManager()->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldSetAndGetProviderConfiguration(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $configPayload = ['a' => 1, 'b' => 2];

        $this->userConfigManager->setProviderConfig(
            $dummyUser->getId(),
            'test_provider',
            $configPayload
        );

        $this->assertSame(
            $configPayload,
            $this->userConfigManager->getProviderConfig($dummyUser->getId(), 'test_provider')
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldSetAndGetConfiguredProviders(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $providers = ['test_provider1', 'test_provider2'];

        $this->userConfigManager->setProvidersCodes($dummyUser->getId(), $providers);

        $this->assertSame(
            $providers,
            $this->userConfigManager->getProvidersCodes($dummyUser->getId())
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldSetAndGetDefaultProvider(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $provider = 'test_provider';

        $this->userConfigManager->setDefaultProvider($dummyUser->getId(), $provider);

        $this->assertSame(
            $provider,
            $this->userConfigManager->getDefaultProvider($dummyUser->getId())
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldResetProviderConfiguration(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $configPayload = ['a' => 1, 'b' => 2];

        $this->userConfigManager->setProviderConfig(
            $dummyUser->getId(),
            'test_provider',
            $configPayload
        );
        $this->userConfigManager->resetProviderConfig($dummyUser->getId(), 'test_provider');

        $this->assertNull(
            $this->userConfigManager->getProviderConfig($dummyUser->getId(), 'test_provider')
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldActivateProvider(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $configPayload = ['a' => 1, 'b' => 2];
        $this->userConfigManager->setProviderConfig(
            $dummyUser->getId(),
            'test_provider',
            $configPayload
        );

        // Check precondition
        $this->assertFalse(
            $this->userConfigManager->isProviderConfigurationActive($dummyUser->getId(), 'test_provider')
        );

        $this->userConfigManager->activateProviderConfiguration($dummyUser->getId(), 'test_provider');

        $this->assertTrue(
            $this->userConfigManager->isProviderConfigurationActive($dummyUser->getId(), 'test_provider')
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldEncryptConfiguration(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);
        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        /** @var EncryptorInterface $encryptor */
        $encryptor = Bootstrap::getObjectManager()->create(EncryptorInterface::class);

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()->create(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();

        $configPayload = ['a' => 1, 'b' => 2];

        $this->userConfigManager->setProviderConfig(
            $dummyUser->getId(),
            'test_provider',
            $configPayload
        );

        $qry = $connection->select()
            ->from('msp_tfa_user_config', 'encoded_config')
            ->where('user_id = ?', $dummyUser->getId());

        $res = $connection->fetchOne($qry);
        $this->assertSame(
            ['test_provider' => $configPayload],
            $this->serializer->unserialize($encryptor->decrypt($res))
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldGetLegacyNonEncryptedProviderConfiguration(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);

        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()->create(ResourceConnection::class);

        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $tfaUserConfig = $resourceConnection->getTableName('msp_tfa_user_config');
        $connection = $resourceConnection->getConnection();

        $configPayload = ['a' => 1, 'b' => 2];
        $connection->insertOnDuplicate(
            $tfaUserConfig,
            [
                'encoded_config' => $this->serializer->serialize(['test_provider' => $configPayload]),
                'default_provider' => 'test_provider',
                'encoded_providers' => $this->serializer->serialize(['test_Provider']),
                'user_id' => $dummyUser->getId()
            ],
            [
                'encoded_config',
                'default_provider',
                'encoded_providers'
            ]
        );

        $this->assertSame(
            $configPayload,
            $this->userConfigManager->getProviderConfig($dummyUser->getId(), 'test_provider')
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testShouldAddProviderConfiguration(): void
    {
        /** @var AdminUserCollection $dummyUserCollection */
        $dummyUserCollection = Bootstrap::getObjectManager()->create(AdminUserCollection::class);

        $dummyUserCollection->addFieldToFilter('username', 'dummy_username');
        $dummyUser = $dummyUserCollection->getFirstItem();

        $configPayload1 = ['a' => 1, 'b' => 2];
        $configPayload2 = ['c' => 1, 'd' => 2];
        $this->userConfigManager->addProviderConfig(
            $dummyUser->getId(),
            'test_provider1',
            $configPayload1
        );
        $this->userConfigManager->addProviderConfig(
            $dummyUser->getId(),
            'test_provider2',
            $configPayload2
        );

        $this->assertSame(
            $configPayload1,
            $this->userConfigManager->getProviderConfig($dummyUser->getId(), 'test_provider1')
        );
        $this->assertSame(
            $configPayload2,
            $this->userConfigManager->getProviderConfig($dummyUser->getId(), 'test_provider2')
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()->create(ResourceConnection::class);

        $connection = $resourceConnection->getConnection();
        $connection->delete('admin_user');

        parent::tearDown();
    }
}
