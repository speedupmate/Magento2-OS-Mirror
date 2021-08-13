<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Vertex\Data\CustomerInterface;
use Vertex\Data\CustomerInterfaceFactory;
use Vertex\Data\TaxRegistration;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\AddressDeterminer;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\QuoteIsVirtualDeterminer;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Builds a Customer object for use with the Vertex SDK
 */
class CustomerBuilder
{
    /** @var AddressBuilder */
    private $addressBuilder;

    /** @var AddressDeterminer */
    private $addressDeterminer;

    /** @var Config */
    private $config;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var CustomerGroupManagement */
    private $customerGroupManagement;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var ExceptionLogger */
    private $logger;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var QuoteIsVirtualDeterminer */
    private $quoteIsVirtualDeterminer;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    /** @var TaxRegistrationBuilder */
    private $taxRegistrationBuilder;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @param Config $config
     * @param AddressBuilder $addressBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerGroupManagement $customerGroupManagement
     * @param TaxClassNameRepository $taxClassNameRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param ExceptionLogger $logger
     * @param TaxRegistrationBuilder $builder
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressDeterminer $addressDeterminer
     */
    public function __construct(
        Config $config,
        AddressBuilder $addressBuilder,
        CustomerRepositoryInterface $customerRepository,
        CustomerGroupManagement $customerGroupManagement,
        TaxClassNameRepository $taxClassNameRepository,
        CustomerInterfaceFactory $customerFactory,
        ExceptionLogger $logger,
        TaxRegistrationBuilder $builder,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        OrderRepositoryInterface $orderRepository,
        AddressDeterminer $addressDeterminer,
        QuoteIsVirtualDeterminer $quoteIsVirtualDeterminer
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->taxRegistrationBuilder = $builder;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->orderRepository = $orderRepository;
        $this->addressDeterminer = $addressDeterminer;
        $this->quoteIsVirtualDeterminer = $quoteIsVirtualDeterminer;
    }

    /**
     * Create a {@see CustomerInterface} from an {@see Order}
     *
     * @param OrderInterface $order
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    public function buildFromOrder(OrderInterface $order)
    {
        $customer = $this->customerFactory->create();
        $storeCode = $order->getStoreId();
        $customerMapper = $this->mapperFactory->getForClass(CustomerInterface::class, $storeCode);
        $customerId = $order->getCustomerId();

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $this->getShippingFromOrder($order);

        if ($billingAddress !== null) {
            $customer->setAdministrativeDestination($this->buildAddress($billingAddress, $storeCode));
        }
        if ($shippingAddress !== null) {
            $customer->setDestination($this->buildAddress($shippingAddress, $storeCode));
        } elseif ($customer->getAdministrativeDestination() !== null) {
            $customer->setDestination($customer->getAdministrativeDestination());
        }

        $registration = array_filter([
            $this->buildRegistrationFromAddress($billingAddress),
            $this->buildRegistrationFromAddress($shippingAddress),
            $this->buildRegistrationFromCustomer($customerId)
        ]);

        $customer->setTaxRegistrations($registration);

        $code = $this->getCustomerCodeById($customerId, $storeCode);
        $customerCode = $this->stringUtilities->substr($code, 0, $customerMapper->getCustomerCodeMaxLength());
        $customer->setCode($customerCode);

        $orderCustomerTaxClass = $this->taxClassNameRepository->getByCustomerGroupId($order->getCustomerGroupId());
        $taxClass = $orderCustomerTaxClass !== 'None' ?
            $orderCustomerTaxClass : $this->getCustomerClassById($customerId);
        $taxClassName = $this->stringUtilities->substr(
            $taxClass,
            0,
            $customerMapper->getCustomerTaxClassNameMaxLength()
        );
        $customer->setTaxClass($taxClassName);

        return $customer;
    }

    /**
     * This method exists to build addresses based off any number of Magento's
     * Address interfaces.
     *
     * @param AddressInterface|OrderAddressInterface|null $taxAddress
     * @param null|string $storeCode
     * @return \Vertex\Data\AddressInterface|AddressBuilder $addressBuilder
     * @throws ConfigurationException
     */
    private function buildAddress($taxAddress, $storeCode = null)
    {
        if (!($taxAddress instanceof AddressInterface || $taxAddress instanceof OrderAddressInterface)) {
            throw new \InvalidArgumentException(
                '$taxAddress must be one of '
                . AddressInterface::class . ' or ' . OrderAddressInterface::class
            );
        }

        $addressBuilder = $this->addressBuilder->setScopeCode($storeCode)
            ->setStreet($taxAddress->getStreet())
            ->setCity($taxAddress->getCity())
            ->setPostalCode($taxAddress->getPostcode())
            ->setCountryCode($taxAddress->getCountryId());

        $region = $taxAddress->getRegion();
        if ($region instanceof RegionInterface && $region->getRegionId()) {
            $addressBuilder->setRegionId($region->getRegionId());
        } elseif ($region instanceof RegionInterface && $region->getRegion()) {
            $addressBuilder->setRegion($region->getRegion());
        } elseif ($taxAddress->getRegionId()) {
            $addressBuilder->setRegionId($taxAddress->getRegionId());
        } elseif (is_string($region)) {
            $addressBuilder->setRegion($region);
        }

        return $addressBuilder->build();
    }

    /**
     * @param QuoteDetailsInterface $quoteDetails
     * @param string|null $storeCode
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    public function buildFromQuoteDetails(QuoteDetailsInterface $quoteDetails, $storeCode = null)
    {
        $customer = $this->customerFactory->create();
        $customerMapper = $this->mapperFactory->getForClass(CustomerInterface::class, $storeCode);
        $customerId = $quoteDetails->getCustomerId();

        $billingAddress = $this->addressDeterminer->determineAdministrativeDestination(
            $quoteDetails->getBillingAddress(),
            $quoteDetails->getCustomerId()
        );

        $shippingAddress = $this->addressDeterminer->determineDestination(
            $quoteDetails->getShippingAddress(),
            $quoteDetails->getCustomerId()
        );

        if ($billingAddress !== null) {
            $customer->setAdministrativeDestination($this->buildAddress($billingAddress, $storeCode));
        }

        if ($shippingAddress !== null && !$this->quoteIsVirtualDeterminer->isVirtual($quoteDetails)) {
            $customer->setDestination($this->buildAddress($shippingAddress, $storeCode));
        } elseif ($customer->getAdministrativeDestination() !== null) {
            $customer->setDestination($customer->getAdministrativeDestination());
        }

        $registration = array_filter([
            $this->buildRegistrationFromAddress($billingAddress),
            $this->buildRegistrationFromAddress($shippingAddress),
            $this->buildRegistrationFromCustomer($customerId)
        ]);

        $customer->setTaxRegistrations($registration);

        $code = $this->getCustomerCodeById($customerId, $storeCode);
        $customerCode = $this->stringUtilities->substr(
            $code,
            0,
            $customerMapper->getCustomerCodeMaxLength()
        );
        $customer->setCode($customerCode);

        // Determine Tax Class
        $taxClassName = null;

        // Check if we're given the name straight up
        $key = $quoteDetails->getCustomerTaxClassKey();
        if ($key && $key->getType() === TaxClassKeyInterface::TYPE_NAME) {
            $taxClassName = $key->getValue();
        }

        // Otherwise, determine a Tax Class ID to look up the name
        $taxClassId = null;
        if ($quoteDetails->getCustomerTaxClassId()) {
            $taxClassId = $quoteDetails->getCustomerTaxClassId();
        } elseif ($key && $key->getType() === TaxClassKeyInterface::TYPE_ID) {
            $taxClassId = (int)$key->getValue();
        }

        if ($taxClassName === null && $taxClassId !== null) {
            $taxClassName = $this->taxClassNameRepository->getById($taxClassId);
        } elseif ($taxClassName === null) {
            $taxClassName = $this->getCustomerClassById((int)$customerId);
        }

        $taxClassName = $this->stringUtilities->substr(
            $taxClassName,
            0,
            $customerMapper->getCustomerTaxClassNameMaxLength()
        );
        $customer->setTaxClass($taxClassName);

        return $customer;
    }

    /**
     * Retrieve a Customer's Tax Class given their ID
     *
     * @param int $customerId
     * @return string
     */
    private function getCustomerClassById($customerId = 0)
    {
        $customerGroupId = 0;
        $taxClassId = 0;
        try {
            if ($customerId) {
                $customerData = $this->customerRepository->getById($customerId);
                $customerGroupId = $customerData->getGroupId();
            } else {
                $taxClassId = $this->customerGroupManagement->getNotLoggedInGroup()->getTaxClassId();
            }
        } catch (\Exception $e) {
            $this->logger->warning($e);
        }

        return $customerGroupId
            ? $this->taxClassNameRepository->getByCustomerGroupId($customerGroupId)
            : $this->taxClassNameRepository->getById($taxClassId);
    }

    /**
     * Retrieve a Customer's Custom Code given their ID
     *
     * @param int $customerId
     * @param string|null $store
     * @return string|null
     */
    private function getCustomerCodeById($customerId = 0, $store = null)
    {
        if ($customerId === 0 || $customerId === null) {
            return $this->config->getDefaultCustomerCode($store);
        }

        $customerCode = null;
        try {
            $customer = $this->customerRepository->getById($customerId);
            $extensions = $customer->getExtensionAttributes();
            if ($extensions !== null && $extensions->getVertexCustomerCode()) {
                $customerCode = $extensions->getVertexCustomerCode();
            }
        } catch (\Exception $e) {
            $this->logger->warning($e);
        }

        return $customerCode ?: $this->config->getDefaultCustomerCode($store);
    }

    /**
     * Retrieve the shipping address from an Order
     *
     * @param OrderInterface $order
     * @return OrderAddressInterface|null
     */
    private function getShippingFromOrder(OrderInterface $order)
    {
        if ($order instanceof Order && $order->getShippingAddress()) {
            return $order->getShippingAddress();
        }

        return $order->getExtensionAttributes() !== null
        && $order->getExtensionAttributes()->getShippingAssignments()
        && $order->getExtensionAttributes()->getShippingAssignments()[0]
        && $order->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()
            ? $order->getExtensionAttributes()->getShippingAssignments()[0]->getShipping()->getAddress()
            : null;
    }

    /**
     * Return a VAT Tax Registration
     *
     * @param AddressInterface|OrderAddressInterface $taxAddress
     * @return TaxRegistration
     * @throws ConfigurationException
     */
    private function buildRegistrationFromAddress($taxAddress)
    {
        $registration = null;

        if ($taxAddress === null) {
            return null;
        }

        if ($taxAddress instanceof AddressInterface) {
            if ($taxAddress->getVatId()) {
                $registration =  $this->taxRegistrationBuilder->buildFromCustomerAddress($taxAddress);
            }
        } elseif ($taxAddress instanceof OrderAddressInterface) {
            $registration = null;
            $order = $this->orderRepository->get($taxAddress->getParentId());
            if ($order) {
                $registration = $this->taxRegistrationBuilder->buildFromOrderAddress(
                    $taxAddress,
                    $order->getCustomerTaxvat()
                );
            }
        } else {
            throw new \InvalidArgumentException(
                'taxAddress must be one of AddressInterface, OrderAddressInterface'
            );
        }

        return $registration;
    }

    /**
     * Load VAT Tax Registration from Customer data
     *
     * @param int $customerId
     * @return TaxRegistration|null
     */
    private function buildRegistrationFromCustomer($customerId)
    {
        $registration = null;

        if (!$customerId) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer->getTaxvat()) {
                $registration = $this->taxRegistrationBuilder->buildFromCustomer($customer);
            }
        } catch (\Exception $e) {
            $this->logger->warning($e);
        }

        return $registration;
    }
}
