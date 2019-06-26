<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Config\Backend;

use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\Helper\Bootstrap;
use Temando\Shipping\Model\Config\Backend\Active\ApiConnection;
use Temando\Shipping\Model\Config\Backend\Active\CredentialsValidator;
use Temando\Shipping\Rest\Authentication;

/**
 * Temando Carrier Enabled Config Backend Model Test
 *
 * @package  Temando\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class CredentialsValidationTest extends \PHPUnit\Framework\TestCase
{
    const ENDPOINT = 'https://auth.temando.io/';
    const ENDPOINT_INVALID = 'gopher://127.0.0.1';

    const ENDPOINT_INVALID_HOST_1 = 'https://auth.temando.com';
    const ENDPOINT_INVALID_HOST_2 = 'https://mitm.proxy/temando.io';

    const CHECKOUT_DISABLED = '0';
    const CHECKOUT_ENABLED = '1';

    const USER_VALID = 'alice';
    const USER_INVALID = 'malice';

    const PASSWORD_VALID = 'openSesame';
    const PASSWORD_INVALID = 'computerSaysNo';

    /**
     * @return CredentialsValidator
     */
    private function getValidator()
    {
        $authTest = $this->getMockBuilder(ApiConnection::class)
            ->setMethods(['test'])
            ->disableOriginalConstructor()
            ->getMock();

        $authTest
            ->expects($this->any())
            ->method('test')
            ->willReturnMap([
                [self::ENDPOINT, '', self::PASSWORD_INVALID, false],
                [self::ENDPOINT, self::USER_INVALID, '', false],
                [self::ENDPOINT, self::USER_INVALID, self::PASSWORD_INVALID, false],
                [self::ENDPOINT, '', self::PASSWORD_VALID, false],
                [self::ENDPOINT, self::USER_VALID, '', false],
                [self::ENDPOINT, self::USER_VALID, self::PASSWORD_VALID, true],
            ]);

        $validator = Bootstrap::getObjectManager()->create(CredentialsValidator::class, [
            'connection' => $authTest,
        ]);

        return $validator;
    }

    /**
     * @test
     */
    public function canSaveWithCheckoutDisabledAndApiEndpointEmpty()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => '',
                    'account_id' => '',
                    'bearer_token' => '',
                ]
            ]
        ]);

        // just assert no exception is thrown
        $this->assertSame($backendModel, $backendModel->validateBeforeSave());
    }

    /**
     * @test
     */
    public function canSaveWithCheckoutDisabledAndCredentialsEmpty()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => '',
                    'bearer_token' => '',
                ]
            ]
        ]);

        // just assert no exception is thrown
        $this->assertSame($backendModel, $backendModel->validateBeforeSave());
    }

    /**
     * @test
     */
    public function canSaveWithCheckoutDisabledAndCredentialsValid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);

        // just assert no exception is thrown
        $this->assertSame($backendModel, $backendModel->validateBeforeSave());
    }

    /**
     * @test
     */
    public function canSaveWithCheckoutEnabledAndCredentialsValid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);

        // just assert no exception is thrown
        $this->assertSame($backendModel, $backendModel->validateBeforeSave());
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutDisabledAndPasswordInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_INVALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutDisabledAndUserInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_INVALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutDisabledAndCredentialsInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_DISABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_INVALID,
                    'bearer_token' => self::PASSWORD_INVALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutEnabledAndCredentialsEmpty()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => '',
                    'bearer_token' => '',
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Please set API credentials before enabling Magento Shipping.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutEnabledAndPasswordInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_INVALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutEnabledAndUserInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_INVALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithCheckoutEnabledAndCredentialsInvalid()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_INVALID,
                    'bearer_token' => self::PASSWORD_INVALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Magento Shipping authentication failed. Please check your credentials.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function canSaveWithValidEndpointProtocol()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);

        // just assert no exception is thrown
        $this->assertSame($backendModel, $backendModel->validateBeforeSave());
    }

    /**
     * @test
     */
    public function cannotSaveWithInvalidEndpointProtocol()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT_INVALID,
                    'account_id' => self::USER_INVALID,
                    'bearer_token' => self::PASSWORD_INVALID,
                ]
            ]
        ]);

        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Please enter a valid URL. Protocol (http://, https://) is required.');
        $backendModel->validateBeforeSave();
    }

    /**
     * @test
     */
    public function cannotSaveWithInvalidEndpointHostname()
    {
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT_INVALID_HOST_1,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);
        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Please enter a valid URL. Protocol (http://, https://) is required.');
        $backendModel->validateBeforeSave();
        $backendModel = Bootstrap::getObjectManager()->create(Active::class, [
            'validationRules' => $this->getValidator(),
            'data' => [
                'value' => self::CHECKOUT_ENABLED,
                'fieldset_data' => [
                    'session_endpoint' => self::ENDPOINT_INVALID_HOST_2,
                    'account_id' => self::USER_VALID,
                    'bearer_token' => self::PASSWORD_VALID,
                ]
            ]
        ]);
        $this->expectException(\Magento\Framework\Validator\Exception::class);
        $this->expectExceptionMessage('Please enter a valid URL. Protocol (http://, https://) is required.');
        $backendModel->validateBeforeSave();
    }
}
