<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

namespace Vertex\Tax\Test\Integration\Scenarios;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\StateException;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Model\ShippingMethodManagementInterface;
use Magento\Store\Model\Store;
use Vertex\Tax\Test\Integration\Builder\CartBuilder;
use Vertex\Tax\Test\Integration\Builder\CustomerBuilder;
use Vertex\Tax\Test\Integration\Builder\ProductBuilder;

/**
 * Create a quote with payment method and Invalid Address
 */
class QuoteWithPaymentAndInvalidAddress
{
    const INVALID_POSTAL_CODE = 'invalid Code';

    /** @var CustomerBuilder */
    private $customerBuilder;

    /** @var ProductBuilder */
    private $productBuilder;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CartBuilder */
    private $cartBuilder;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var ShippingMethodManagementInterface */
    private $shippingMethodManagement;

    /** @var PaymentInterfaceFactory */
    private $paymentFactory;

    /** @var PaymentInformationManagementInterface */
    private $paymentInformationManagement;

    /**
     * @param CustomerBuilder $customerBuilder
     * @param ProductBuilder $productBuilder
     * @param AddressInterfaceFactory $addressFactory
     * @param CartBuilder $cartBuilder
     * @param CartRepositoryInterface $cartRepository
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param PaymentInterfaceFactory $paymentFactory
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     */
    public function __construct(
        CustomerBuilder $customerBuilder,
        ProductBuilder $productBuilder,
        AddressInterfaceFactory $addressFactory,
        CartBuilder $cartBuilder,
        CartRepositoryInterface $cartRepository,
        ShippingMethodManagementInterface $shippingMethodManagement,
        PaymentInterfaceFactory $paymentFactory,
        PaymentInformationManagementInterface $paymentInformationManagement
    ) {
        $this->customerBuilder = $customerBuilder;
        $this->productBuilder = $productBuilder;
        $this->addressFactory = $addressFactory;
        $this->cartBuilder = $cartBuilder;
        $this->cartRepository = $cartRepository;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->paymentFactory = $paymentFactory;
        $this->paymentInformationManagement = $paymentInformationManagement;
    }

    /**
     * Create fake cart
     *
     * @param string|int $fakeReservedOrderId
     * @param int $storeId
     * @return CartInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function create($fakeReservedOrderId, $storeId = Store::DISTRO_STORE_ID)
    {

        $customer = $this->customerBuilder->createExampleCustomer();

        $product = $this->productBuilder->createExampleProduct();

        // create address
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $address->setCity('West Chester');
        $address->setCountryId('US');
        $address->setFirstname('John');
        $address->setLastname('Doe');
        $address->setPostcode(static::INVALID_POSTAL_CODE);
        $address->setRegion('Colorado');
        $address->setRegionCode('CO');
        $address->setRegionId(13);
        $address->setStreet(['233 West Gay St']);
        $address->setTelephone('1234567890');

        // create cart
        $cart = $this->cartBuilder->setItems()
            ->addItem($product)
            ->create($customer->getId());
        $cart->setReservedOrderId($fakeReservedOrderId);
        $cart->setStoreId($storeId);
        $cart->setBillingAddress($address);

        $this->cartRepository->save($cart);

        $cart = $this->cartRepository->getForCustomer($customer->getId());

        //set shipping method
        $this->shippingMethodManagement->estimateByExtendedAddress($cart->getId(), $address);

        //set payment method
        $paymentMethod = $this->paymentFactory->create();
        $paymentMethod->setMethod(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);

        $this->paymentInformationManagement->savePaymentInformation(
            $cart->getId(),
            $paymentMethod,
            $address
        );

        return $cart;
    }
}
