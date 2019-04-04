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
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Vertex\Data\CustomerInterface;
use Vertex\Data\CustomerInterfaceFactory;
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
        TaxRegistrationBuilder $builder
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
    }

    /**
     * Create a properly formatted array of Customer Data for a Vertex API
     *
     * @param AddressInterface $taxAddress
     * @param int|null $customerId
     * @param int|null $taxClassId
     * @param string|null $storeCode
     * @return CustomerInterface
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
     */
    public function buildFromOrder(Order $order)
    {
        $orderAddress = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        $customer = $this->buildFromOrderAddress($orderAddress);

        $customer->setTaxClass($this->getCustomerClassById($order->getCustomerId()));
        $customer->setCode($this->getCustomerCodeById($order->getCustomerId()));

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
     */
    private function buildFromAddress($taxAddress = null, $customerId = null, $taxClassId = null, $storeCode = null)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();

        if ($taxAddress !== null) {
            if (!($taxAddress instanceof AddressInterface || $taxAddress instanceof OrderAddressInterface)) {
                throw new \InvalidArgumentException(
                    '$taxAddress must be one of '
                    . AddressInterface::class . ' or ' . OrderAddressInterface::class
                );
            }

            $addressBuilder = $this->addressBuilder
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
            } else if (is_string($region)) {
                $addressBuilder->setRegion($region);
            }

            $customer->setDestination($addressBuilder->build());

            if ($taxAddress->getVatId()) {
                $this->updateCustomerWithRegistration($customer, $taxAddress);
            }
        }

        $customer->setCode($this->getCustomerCodeById($customerId, $storeCode));

        $class = $taxClassId
            ? $this->taxClassNameRepository->getById($taxClassId)
            : $this->getCustomerClassById($customerId);

        $customer->setTaxClass($class);

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
     * @return CustomerInterface
     */
    private function updateCustomerWithRegistration($customer, $taxAddress)
    {
        if ($taxAddress instanceof AddressInterface) {
            $registration = $this->taxRegistrationBuilder->buildFromCustomerAddress($taxAddress);
        } elseif ($taxAddress instanceof OrderAddressInterface) {
            $registration = $this->taxRegistrationBuilder->buildFromOrderAddress($taxAddress);
        } else {
            throw new \InvalidArgumentException('taxAddress must be one of AddressInterface, OrderAddressInterface');
        }

        $customer->setTaxRegistrations([$registration]);

        return $customer;
    }
}
