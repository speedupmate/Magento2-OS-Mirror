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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterfaceFactory;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Vertex\Tax\Test\Integration\Builder\GuestCartBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilder;
use Vertex\Tax\Test\Integration\Builder\TaxClassBuilder;
use Vertex\Tax\Test\Integration\TestCase;

/**
 * Ensure that when totals are collected our tax request being sent to Vertex also sends the Product's Tax Class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTaxClassSentToVertexTest extends TestCase
{
    const PRODUCT_SKU = ProductBuilder::EXAMPLE_PRODUCT_SKU;
    const TAX_CLASS_NAME = 'Testable Tax Class';

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var GuestCartBuilder */
    private $guestCartBuilder;

    /** @var ProductBuilder */
    private $productBuilder;

    /** @var StockRegistryStorage */
    private $stockRegistryStorage;

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
        $this->stockRegistryStorage = $this->getObject(StockRegistryStorage::class);

        $this->productBuilder = $this->getObject(ProductBuilder::class);
        $this->taxClassBuilder = $this->getObject(TaxClassBuilder::class);
        $this->guestCartBuilder = $this->getObject(GuestCartBuilder::class);
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
    public function testOutgoingRequestContainsProductTaxClass()
    {
        $taxClassId = $this->createTaxClass(static::TAX_CLASS_NAME);
        $productMock = $this->createProduct($taxClassId);
        $cart = $this->createCartWithProduct($productMock);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()->addMethods(['CalculateTax70'])->getMock();
        $soapClient->expects($this->atLeastOnce())
            ->method('CalculateTax70')
            ->with(
                $this->callback(
                    function (\stdClass $request) use ($productMock) {
                        $lineItems = $request->QuotationRequest->LineItem;
                        foreach ($lineItems as $lineItem) {
                            $product = $lineItem->Product;
                            if ($product->_ === $productMock->getSku()) {
                                $this->assertEquals(static::TAX_CLASS_NAME, $product->productClass);
                                return true;
                            }
                        }
                        $this->fail(
                            'Product with SKU "' . $productMock->getSku() . '" not found in Vertex Request:' . PHP_EOL
                            . print_r($request, true)
                        );
                        return false;
                    }
                )
            )
            ->willReturn(new \stdClass());
        $this->getSoapFactory()->setSoapClient($soapClient);

        $address = $this->createShippingAddress();

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
     * @return CartInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function createCartWithProduct(ProductInterface $product)
    {
        return $this->guestCartBuilder->setItems()
            ->addItem($product)
            ->create();
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
     * @return AddressInterface
     */
    private function createShippingAddress()
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
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
     * @return string Tax Class ID
     * @throws InputException
     * @throws LocalizedException
     */
    private function createTaxClass($taxClassName)
    {
        return $this->taxClassBuilder->createTaxClass($taxClassName, 'PRODUCT');
    }
}
