<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\CountryRestriction;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Vertex\Tax\Test\Integration\Builder\CartBuilder;
use Vertex\Tax\Test\Integration\Builder\CustomerBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilder;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that the "use for countries shipping to" configuration setting is respected
 */
class CountryRestrictionTest extends TestCase
{
    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CartBuilder */
    private $cartBuilder;

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var ProductBuilder */
    private $productBuilder;

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
        $this->customerBuilder = $this->getObject(CustomerBuilder::class);
        $this->productBuilder = $this->getObject(ProductBuilder::class);
        $this->cartBuilder = $this->getObject(CartBuilder::class);
    }

    /**
     * Ensure that the "use for countries shipping to" configuration setting is respected
     *
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     * @magentoConfigFixture default_store tax/vertex_settings/allowed_countries MX
     * @magentoDbIsolation enabled
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    public function testUsesVertexWhenCountryIsEnabled()
    {
        $this->markTestIncomplete('To be fixed in scope of VRTX-754');

        $soapClient = $this->getMockBuilder(\SoapClient::class)->disableOriginalConstructor()
            ->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->willReturn(new \stdClass());

        $this->getSoapFactory()->setSoapClient($soapClient);

        $this->runTotalsWithMexicoAddress();
    }

    /**
     * Ensure that the "use for countries shipping to" configuration setting is respected
     *
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     * @magentoConfigFixture default_store tax/vertex_settings/allowed_countries GB
     * @magentoDbIsolation enabled
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    public function testDoesNotUseVertexWhenCountryIsDisabled()
    {
        $soapClient = $this->getMockBuilder(\SoapClient::class)->disableOriginalConstructor()
            ->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->never())
            ->method('CalculateTax70')
            ->willReturn(new \stdClass());

        $this->getSoapFactory()->setSoapClient($soapClient);

        $this->runTotalsWithMexicoAddress();
    }

    /**
     * Create a cart using a Mexico address and call totals against it
     *
     * Shared functionality for tests
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputMismatchException
     */
    private function runTotalsWithMexicoAddress()
    {
        $customer = $this->customerBuilder->createExampleCustomer();
        $address = $this->createAddress($customer->getId());

        $product = $this->productBuilder->createExampleProduct();
        $cart = $this->cartBuilder->setItems()
            ->addItem($product)
            ->create($customer->getId());

        $cart->setBillingAddress($address);

        /** @var TotalsInformationInterface $totalsInfo */
        $totalsInfo = $this->totalsInformationFactory->create();
        $totalsInfo->setAddress($address);
        $totalsInfo->setShippingCarrierCode('flatrate');
        $totalsInfo->setShippingMethodCode('flatrate');

        $this->totalManager->calculate($cart->getId(), $totalsInfo);
    }

    /**
     * Create Mexico Customer Address
     *
     * @param int $customerId
     * @return AddressInterface
     */
    private function createAddress($customerId)
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $address->setCustomerId($customerId);
        $address->setStreet(['José María Pino Suárez 30', 'Centro Histórico, Centro']);
        $address->setCity('Ciudad de México');
        $address->setRegionCode('CDMX');
        $address->setRegion('Ciudad de México');
        $address->setPostcode('06060');
        $address->setCountryId('MX');
        $address->setFirstname(CustomerBuilder::EXAMPLE_CUSTOMER_FIRSTNAME);
        $address->setLastname(CustomerBuilder::EXAMPLE_CUSTOMER_LASTNAME);
        return $address;
    }
}
