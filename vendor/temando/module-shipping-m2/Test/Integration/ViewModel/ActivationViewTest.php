<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\ViewModel\Carrier;

use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\ViewModel\Config\Activation;

/**
 * Temando Activation View Model Test
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ActivationViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Assert that view model passes through merchant registered state.
     *
     * @test
     * @magentoConfigFixture default/carriers/temando/account_id accountId
     * @magentoConfigFixture default/carriers/temando/bearer_token bearerToken
     */
    public function merchantIsRegistered()
    {
        /** @var Activation $viewModel */
        $viewModel = Bootstrap::getObjectManager()->get(Activation::class);
        self::assertTrue($viewModel->isMerchantRegistered());
    }

    /**
     * Assert that view model passes through merchant registered state.
     *
     * @test
     */
    public function merchantIsNotRegistered()
    {
        /** @var Activation $viewModel */
        $viewModel = Bootstrap::getObjectManager()->get(Activation::class);
        self::assertFalse($viewModel->isMerchantRegistered());
    }

    /**
     * Assert that registration url is passed through from config.
     *
     * @test
     * @magentoConfigFixture default/carriers/temando/register_account_url http://example.org/
     */
    public function getRegisterAccountUrl()
    {
        /** @var Activation $viewModel */
        $viewModel = Bootstrap::getObjectManager()->get(Activation::class);
        self::assertEquals('http://example.org/', $viewModel->getRegisterAccountUrl());
    }

    /**
     * Assert that account redirect url is properly created.
     *
     * @test
     */
    public function getAccountRedirectUrl()
    {
        /** @var Activation $viewModel */
        $viewModel = Bootstrap::getObjectManager()->get(Activation::class);

        self::assertStringStartsWith('http', $viewModel->getAccountRedirectUrl());
        self::assertContains('configuration_portal/account', $viewModel->getAccountRedirectUrl());
    }
}
