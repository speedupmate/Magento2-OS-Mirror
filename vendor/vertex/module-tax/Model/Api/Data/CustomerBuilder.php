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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Vertex\Data\CustomerInterface;
use Vertex\Data\CustomerInterfaceFactory;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\TaxClassNameRepository;

/**
 * Builds a Customer object for use with the Vertex SDK
 */
class CustomerBuilder
{
    /** @var AddressBuilder */
    private $addressBuilder;

    /** @var Config */
    private $config;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var CustomerGroupManagement */
    private $customerGroupManagement;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var ExceptionLogger */
    private $logger;

    /** @var TaxClassNameRepository */
    private $taxClassNameRepository;

    /** @var TaxRegistrationBuilder */
    private $taxRegistrationBuilder;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @param Config $config
     * @param AddressBuilder $addressBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerGroupManagement $customerGroupManagement
     * @param TaxClassNameRepository $taxClassNameRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param ExceptionLogger $logger
     * @param GroupRepositoryInterface $groupRepository
     * @param TaxRegistrationBuilder $builder
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Config $config,
        AddressBuilder $addressBuilder,
        CustomerRepositoryInterface $customerRepository,
        CustomerGroupManagement $customerGroupManagement,
        TaxClassNameRepository $taxClassNameRepository,
        CustomerInterfaceFactory $customerFactory,
        ExceptionLogger $logger,
        GroupRepositoryInterface $groupRepository,
        TaxRegistrationBuilder $builder,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->addressBuilder = $addressBuilder;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->taxClassNameRepository = $taxClassNameRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->groupRepository = $groupRepository;
        $this->taxRegistrationBuilder = $builder;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create a properly formatted array of Customer Data for a Vertex API
     *
     * @param AddressInterface $taxAddress
     * @param int|null $customerId
     * @param int|null $taxClassId
     * @param string|null $storeCode
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    public function buildFromCustomerAddress(
        AddressInterface $taxAddress = null,
        $customerId = null,
        $taxClassId = null,
        $storeCode = null
    ) {
        return $this->buildFromAddress($taxAddress, $customerId, $taxClassId, $storeCode);
    }

    /**
     * Create a {@see CustomerInterface} from an {@see Order}
     *
     * @param Order $order
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    public function buildFromOrder(Order $order)
    {
        $orderAddress = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        $customer = $this->buildFromOrderAddress($orderAddress);

        $storeCode = $order->getStoreId();
        $customerMapper = $this->mapperFactory->getForClass(CustomerInterface::class, $storeCode);

        $taxClass = $this->getCustomerClassById($order->getCustomerId());
        $taxClassName = $this->stringUtilities->substr(
            $taxClass,
            0,
            $customerMapper->getCustomerTaxClassNameMaxLength()
        );
        $customer->setTaxClass($taxClassName);

        $code = $this->getCustomerCodeById($order->getCustomerId());
        $customerCode = $this->stringUtilities->substr($code, 0, $customerMapper->getCustomerCodeMaxLength());
        $customer->setCode($customerCode);

        return $customer;
    }

    /**
     * Create a properly formatted array of Customer Data for a Vertex API
     *
     * @param OrderAddressInterface $taxAddress
     * @param int|null $customerId
     * @param int|null $customerGroupId
     * @param string|null $storeCode
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    public function buildFromOrderAddress(
        OrderAddressInterface $taxAddress = null,
        $customerId = null,
        $customerGroupId = null,
        $storeCode = null
    ) {
        try {
            $group = $customerGroupId ? $this->groupRepository->getById($customerGroupId) : null;
        } catch (\Exception $e) {
            $group = null;
        }
        $taxClassId = $group ? $group->getTaxClassId() : null;
        return $this->buildFromAddress($taxAddress, $customerId, $taxClassId, $storeCode);
    }

    /**
     * Create a properly formatted array of Customer Data for the Vertex API
     *
     * This method exists to build addresses based off any number of Magento's
     * Address interfaces.
     *
     * @param AddressInterface|OrderAddressInterface|null $taxAddress
     * @param int $customerId
     * @param int $taxClassId
     * @param string $storeCode
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    private function buildFromAddress($taxAddress = null, $customerId = null, $taxClassId = null, $storeCode = null)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $customerMapper = $this->mapperFactory->getForClass(CustomerInterface::class, $storeCode);

        if ($taxAddress !== null) {
            if (!($taxAddress instanceof AddressInterface || $taxAddress instanceof OrderAddressInterface)) {
                throw new \InvalidArgumentException(
                    '$taxAddress must be one of '
                    . AddressInterface::class . ' or ' . OrderAddressInterface::class
                );
            }

            $addressBuilder = $this->addressBuilder
                ->setScopeCode($storeCode)
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

            $customer->setDestination($addressBuilder->build());

            $this->updateCustomerWithRegistration($customer, $taxAddress, $customerId);
        }

        $code = $this->getCustomerCodeById($customerId, $storeCode);
        $customerCode = $this->stringUtilities->substr($code, 0, $customerMapper->getCustomerCodeMaxLength());
        $customer->setCode($customerCode);

        $class = $taxClassId
            ? $this->taxClassNameRepository->getById($taxClassId)
            : $this->getCustomerClassById($customerId);

        $taxClassName = $this->stringUtilities->substr($class, 0, $customerMapper->getCustomerTaxClassNameMaxLength());
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
     * Add a VAT Tax Registration to a Customer record
     *
     * @param CustomerInterface $customer
     * @param AddressInterface|OrderAddressInterface $taxAddress
     * @param int $customerId
     * @return CustomerInterface
     * @throws ConfigurationException
     */
    private function updateCustomerWithRegistration($customer, $taxAddress, $customerId = 0)
    {
        $registration = null;

        if ($taxAddress instanceof AddressInterface) {
            if ($taxAddress->getVatId()) {
                $registration = $this->taxRegistrationBuilder->buildFromCustomerAddress($taxAddress);
            } elseif ($taxAddress->getCustomerId() || $customerId) {
                $registration = $this->buildRegistrationFromCustomer(
                    $taxAddress->getCustomerId() ?: $customerId
                );
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
            throw new \InvalidArgumentException('taxAddress must be one of AddressInterface, OrderAddressInterface');
        }

        if ($registration !== null) {
            $customer->setTaxRegistrations([$registration]);
        }

        return $customer;
    }

    /**
     * Load VAT Tax Registration from Customer data
     *
     * @param int $customerId
     * @return \Vertex\Data\TaxRegistration|null
     */
    private function buildRegistrationFromCustomer($customerId)
    {
        $registration = null;

        if (!$customerId) {
            throw new \InvalidArgumentException('Customer ID not provided');
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
