<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReCaptchaCustomer\Test\Api;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test customer login graphql mutation
 */
class LoginGraphQlTest extends GraphQlAbstract
{

    /**
     * Test that recaptcha is required
     *
     * @magentoConfigFixture default_store customer/captcha/enable 0
     * @magentoConfigFixture base_website recaptcha_frontend/type_invisible/public_key test_public_key
     * @magentoConfigFixture base_website recaptcha_frontend/type_invisible/private_key test_private_key
     * @magentoConfigFixture base_website recaptcha_frontend/type_for/customer_login invisible
     */
    public function testRequired(): void
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('ReCaptcha validation failed, please try again');

        $this->graphQlMutation(<<<QUERY
mutation {
  generateCustomerToken(email:"John.Doe@example.com",password:"abc123") {
    token
  }
}
QUERY
        );
    }
}
