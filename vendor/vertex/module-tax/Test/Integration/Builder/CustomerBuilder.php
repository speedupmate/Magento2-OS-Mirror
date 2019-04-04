<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Integration\Builder;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

/**
 * Build a customer entity
 */
class CustomerBuilder
{
    const EXAMPLE_CUSTOMER_EMAIL = 'jdoe@host.local';
    const EXAMPLE_CUSTOMER_FIRSTNAME = 'John';
    const EXAMPLE_CUSTOMER_LASTNAME = 'Doe';

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Create a customer
     *
     * @param callable $customerConfiguration Receives 1 parameter of CustomerInterface.
     *      Should return a CustomerInterface.
     * @return CustomerInterface
     * @throws \TypeError
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer(callable $customerConfiguration)
    {
        /** @var CustomerInterface $customer */
        $customer = $customerConfiguration($this->customerFactory->create());

        if (!($customer instanceof CustomerInterface)) {
            throw new \TypeError('Result of createCustomer callback must return a CustomerInterface');
        }

        return $this->customerRepository->save($customer);
    }

    /**
     * Creates a generic customer
     *
     * Identity: John Doe <jdoe@host.local>
     *
     * @param callable $customerConfiguration Receives 1 parameter of CustomerInterface.
     *      Should return a CustomerInterface.
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function createExampleCustomer(callable $customerConfiguration = null)
    {
        return $this->createCustomer(
            function (CustomerInterface $customer) use ($customerConfiguration) {
                $customer->setFirstname(static::EXAMPLE_CUSTOMER_FIRSTNAME);
                $customer->setLastname(static::EXAMPLE_CUSTOMER_LASTNAME);
                $customer->setEmail(static::EXAMPLE_CUSTOMER_EMAIL);
                return $customerConfiguration !== null ? $customerConfiguration($customer) : $customer;
            }
        );
    }
}
