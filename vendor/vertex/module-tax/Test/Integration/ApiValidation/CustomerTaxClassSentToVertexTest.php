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
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Vertex\Tax\Test\Integration\Builder\CartBuilder;
use Vertex\Tax\Test\Integration\Builder\CustomerBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilder;
use Vertex\Tax\Test\Integration\Builder\TaxClassBuilder;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that when totals are collected our tax request being sent to Vertex also sends the Customer's Tax Class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTaxClassSentToVertexTest extends TestCase
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
        $this->productBuilder = $this->getObject(ProductBuilder::class);
        $this->taxClassBuilder = $this->getObject(TaxClassBuilder::class);
        $this->cartBuilder = $this->getObject(CartBuilder::class);
    }

    /**
     * Ensure that when totals are collected our tax request being sent to Vertex also sends the Product's Tax Class
     *
     * @magentoConfigFixture default_store tax/vertex_settings/enable_vertex 1
     * @magentoConfigFixture default_store tax/vertex_settings/trustedId 0123456789ABCDEF
     * @magentoConfigFixture default_store tax/vertex_settings/api_url https://example.org/CalculateTax70
     * @magentoDbIsolation enabled
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testOutgoingRequestContainsCustomerTaxClass()
    {
        $productTaxClassId = $this->createTaxClass('Testable Product Tax Class', 'PRODUCT');
        $customerTaxClassId = $this->createTaxClass(static::TAX_CLASS_NAME);
        $product = $this->createProduct($productTaxClassId);
        $customer = $this->createCustomer($customerTaxClassId);
        $cart = $this->createCartWithProduct($product, $customer->getId());

        $soapClient = $this->getMockBuilder(\SoapClient::class)->disableOriginalConstructor()
            ->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) {
                        $customerData = $request->QuotationRequest->Customer;
                        if (empty($customerData->CustomerCode) || empty($customerData->CustomerCode->classCode)) {
                            $this->fail(
                                'Customer with tax class "' . static::TAX_CLASS_NAME . '" not found in Vertex Request:'
                                . PHP_EOL
                                . print_r($request, true)
                            );
                            return false;
                        }

                        $this->assertEquals(static::TAX_CLASS_NAME, $customerData->CustomerCode->classCode);
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
     * Creates a guest cart containing 1 of the provided product
     *
     * @param ProductInterface $product
     * @param int $customerId
     * @return CartInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function createCartWithProduct(ProductInterface $product, $customerId)
    {
        return $this->cartBuilder->setItems()
            ->addItem($product)
            ->create($customerId);
    }

    /**
     * Create and save our test's needed Customer
     *
     * @param int|string $taxClassId
     * @return CustomerInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    private function createCustomer($taxClassId)
    {
        $groupId = $this->createCustomerGroup($taxClassId);

        return $this->customerBuilder->createExampleCustomer(
            function (CustomerInterface $customer) use ($groupId) {
                $customer->setGroupId($groupId);
                return $customer;
            }
        );
    }

    /**
     * Create a new customer group for the given tax class ID.
     *
     * @param $taxClassId
     * @return int
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    private function createCustomerGroup($taxClassId)
    {
        $group = $this->customerGroupFactory->create();

        $group->setCode('Test Customer Group');
        $group->setTaxClassId($taxClassId);

        return $this->customerGroupRepository->save($group)->getId();
    }

    /**
     * Create and save our test's needed Product
     *
     * @param int|string $taxClassId
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function createProduct($taxClassId)
    {
        return $this->productBuilder->createExampleProduct(
            function (ProductInterface $product) use ($taxClassId) {
                $product->setCustomAttribute('tax_class_id', $taxClassId);

                return $product;
            }
        );
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

    /**
     * Create and save our test's needed tax class
     *
     * @param string $taxClassName
     * @param string $type
     * @return string Tax Class ID
     * @throws InputException
     * @throws LocalizedException
     */
    private function createTaxClass($taxClassName, $type = 'CUSTOMER')
    {
        return $this->taxClassBuilder->createTaxClass($taxClassName, $type);
    }
}
