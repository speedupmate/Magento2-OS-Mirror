<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\ApiValidation;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Vertex\Tax\Test\Integration\Builder\CartBuilder;
use Vertex\Tax\Test\Integration\Builder\CartBuilderFactory;
use Vertex\Tax\Test\Integration\Builder\CustomerBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilderFactory;
use Vertex\Tax\Test\Integration\Builder\TaxClassBuilder;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that when totals are collected the correct currency is sent to Vertex
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BaseCurrencyCodeSentToVertexTest extends TestCase
{
    const TAX_CLASS_NAME = 'Testable Tax Class';

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CartBuilder */
    private $cartBuilder;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var GroupInterfaceFactory */
    private $customerGroupFactory;

    /** @var GroupRepositoryInterface */
    private $customerGroupRepository;

    /** @var ProductBuilder */
    private $productBuilder;

    /** @var TaxClassBuilder */
    private $taxClassBuilder;

    /** @var TotalsInformationManagementInterface */
    private $totalManager;

    /** @var TotalsInformationInterfaceFactory */
    private $totalsInformationFactory;

    /**
     * Fetch objects necessary for running our test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->totalManager = $this->getObject(TotalsInformationManagementInterface::class);
        $this->totalsInformationFactory = $this->getObject(TotalsInformationInterfaceFactory::class);
        $this->addressFactory = $this->getObject(AddressInterfaceFactory::class);
        $this->customerGroupFactory = $this->getObject(GroupInterfaceFactory::class);
        $this->customerGroupRepository = $this->getObject(GroupRepositoryInterface::class);
        $this->customerBuilder = $this->getObject(CustomerBuilder::class);
        $this->productBuilder = $this->getObject(ProductBuilderFactory::class)->create();
        $this->taxClassBuilder = $this->getObject(TaxClassBuilder::class);
        $this->cartBuilder = $this->getObject(CartBuilderFactory::class)->create();
    }

    /**
     * Ensure that when totals are collected our tax request being sent to Vertex sends a base currency of CNY
     *
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     * @magentoConfigFixture current_store currency/options/base EUR
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation enabled
     * @magentoCache all disabled
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testBaseCurrencyOfEUR()
    {
        $product = $this->productBuilder->createExampleProduct();
        $customer = $this->customerBuilder->createExampleCustomer();
        $cart = $this->cartBuilder->setItems([])
            ->addItem($product)
            ->create($customer->getId());

        $soapClient = $this->getMockBuilder(\SoapClient::class)->disableOriginalConstructor()
            ->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) {
                        $currency = $request->QuotationRequest->Currency;
                        if (empty($currency) || empty($currency->isoCurrencyCodeAlpha)) {
                            $this->fail('Currency code not set');
                            return false;
                        }
                        $this->assertEquals('EUR', $request->QuotationRequest->Currency->isoCurrencyCodeAlpha);
                        return true;
                    }
                )
            )
            ->willReturn(new \stdClass());
        $this->getSoapFactory()->setSoapClient($soapClient);

        $address = $this->createShippingAddress($customer->getId());

        /** @var TotalsInformationInterface $totalsInfo */
        $totalsInfo = $this->totalsInformationFactory->create();
        $totalsInfo->setAddress($address);
        $totalsInfo->setShippingCarrierCode('flatrate');
        $totalsInfo->setShippingMethodCode('flatrate');

        $this->totalManager->calculate($cart->getId(), $totalsInfo);
    }

    /**
     * Ensure that when totals are collected our tax request being sent to Vertex sends a base currency of USD
     *
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default USD
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation enabled
     * @magentoCache all disabled
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testBaseCurrencyOfUSD()
    {
        $product = $this->productBuilder->createExampleProduct();
        $customer = $this->customerBuilder->createExampleCustomer();
        $cart = $this->cartBuilder->setItems([])
            ->addItem($product)
            ->create($customer->getId());

        $soapClient = $this->getMockBuilder(\SoapClient::class)->disableOriginalConstructor()
            ->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) {
                        $currency = $request->QuotationRequest->Currency;
                        if (empty($currency) || empty($currency->isoCurrencyCodeAlpha)) {
                            $this->fail('Currency code not set');
                            return false;
                        }
                        $this->assertEquals('USD', $request->QuotationRequest->Currency->isoCurrencyCodeAlpha);
                        return true;
                    }
                )
            )
            ->willReturn(new \stdClass());
        $this->getSoapFactory()->setSoapClient($soapClient);

        $address = $this->createShippingAddress($customer->getId());

        /** @var TotalsInformationInterface $totalsInfo */
        $totalsInfo = $this->totalsInformationFactory->create();
        $totalsInfo->setAddress($address);
        $totalsInfo->setShippingCarrierCode('flatrate');
        $totalsInfo->setShippingMethodCode('flatrate');

        $this->totalManager->calculate($cart->getId(), $totalsInfo);
    }

    /**
     * Create a shipping address for our order
     *
     * @param int|null $customerId
     * @return AddressInterface
     */
    private function createShippingAddress($customerId = null)
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $address->setCustomerId($customerId);
        $address->setCity('West Chester');
        $address->setCountryId('US');
        $address->setFirstname('John');
        $address->setLastname('Doe');
        $address->setPostcode('19382');
        $address->setRegion('Pennsylvania');
        $address->setRegionCode('PA');
        $address->setRegionId(51);
        $address->setStreet(['233 West Gay St']);
        $address->setTelephone('1234567890');
        return $address;
    }
}
