<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Customer\Model\Session;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\Braintree\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    const SDK_URL = 'https://js.braintreegateway.com/v2/braintree.js';
    const CLIENT_TOKEN = 'token';
    const MERCHANT_ACCOUNT_ID = '245345';

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $braintreeAdapter;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PayPalConfig $payPalConfig */
        $payPalConfig = $this->getMockBuilder(PayPalConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->braintreeAdapter);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $this->session->method('getStoreId')
            ->willReturn(null);

        /** @var ResolverInterface $localeResolver */
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->configProvider = new ConfigProvider(
            $this->config,
            $payPalConfig,
            $this->braintreeAdapter,
            $localeResolver,
            $this->session,
            $adapterFactory
        );
    }

    /**
     * Run test getConfig method
     *
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
        $this->braintreeAdapter->method('generate')
            ->willReturn(self::CLIENT_TOKEN);

        foreach ($config as $method => $value) {
            $this->config->method($method)
                ->willReturn($value);
        }

        self::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @dataProvider getClientTokenDataProvider
     */
    public function testGetClientToken($merchantAccountId, $params)
    {
        $this->config->method('getMerchantAccountId')
            ->willReturn($merchantAccountId);

        $this->braintreeAdapter->method('generate')
            ->with($params)
            ->willReturn(self::CLIENT_TOKEN);

        self::assertEquals(self::CLIENT_TOKEN, $this->configProvider->getClientToken());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'config' => [
                    'isActive' => true,
                    'getCcTypesMapper' => ['visa' => 'VI', 'american-express'=> 'AE'],
                    'getSdkUrl' => self::SDK_URL,
                    'getCountrySpecificCardTypeConfig' => [
                        'GB' => ['VI', 'AE'],
                        'US' => ['DI', 'JCB']
                    ],
                    'getAvailableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                    'isCvvEnabled' => true,
                    'isVerify3DSecure' => true,
                    'getThresholdAmount' => 20,
                    'get3DSecureSpecificCountries' => ['GB', 'US', 'CA'],
                    'getEnvironment' => 'test-environment',
                    'getKountMerchantId' => 'test-kount-merchant-id',
                    'getMerchantId' => 'test-merchant-id',
                    'hasFraudProtection' => true,
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
                            'isActive' => true,
                            'clientToken' => self::CLIENT_TOKEN,
                            'ccTypesMapper' => ['visa' => 'VI', 'american-express' => 'AE'],
                            'sdkUrl' => self::SDK_URL,
                            'countrySpecificCardTypes' =>[
                                'GB' => ['VI', 'AE'],
                                'US' => ['DI', 'JCB']
                            ],
                            'availableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                            'useCvv' => true,
                            'environment' => 'test-environment',
                            'kountMerchantId' => 'test-kount-merchant-id',
                            'merchantId' => 'test-merchant-id',
                            'hasFraudProtection' => true,
                            'ccVaultCode' => ConfigProvider::CC_VAULT_CODE
                        ],
                        Config::CODE_3DSECURE => [
                            'enabled' => true,
                            'thresholdAmount' => 20,
                            'specificCountries' => ['GB', 'US', 'CA']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getClientTokenDataProvider()
    {
        return [
            [
                'merchantAccountId' => '',
                'params' => []
            ],
            [
                'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                'params' => ['merchantAccountId' => self::MERCHANT_ACCOUNT_ID]
            ]
        ];
    }
}
