<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

/**
 * Determines the address to use for tax calculation
 */
class AddressDeterminer
{
    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var IncompleteAddressDeterminer */
    private $incompleteAddressDeterminer;

    /** @var ExceptionLogger */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param ExceptionLogger $logger
     * @param IncompleteAddressDeterminer $incompleteAddressDeterminer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        ExceptionLogger $logger,
        IncompleteAddressDeterminer $incompleteAddressDeterminer
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->incompleteAddressDeterminer = $incompleteAddressDeterminer;
    }

    /**
     * Determine whether to use the provided address or the customer's default billing
     *
     * @param AddressInterface|QuoteAddressInterface|null $address
     * @param int|null $customerId
     * @return AddressInterface|QuoteAddressInterface|null
     */
    public function determineAdministrativeDestination($address = null, ?int $customerId = null)
    {
        if ($address !== null && !($address instanceof AddressInterface || $address instanceof QuoteAddressInterface)) {
            throw new \InvalidArgumentException(
                '$address must be a Customer or Quote Address.  Is: '
                // gettype() used for debug output and not for checking types
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                . (is_object($address) ? get_class($address) : gettype($address))
            );
        }

        if (!$this->isIncompleteAddress($address)) {
            return $address;
        }

        if (!$customerId) {
            // The address is incomplete and there's nothing to fall back to
            return null;
        }

        return $this->getDefaultBilling($customerId);
    }

    /**
     * Determine whether to use the provided address or the customer's default shipping
     *
     * @param AddressInterface|QuoteAddressInterface|null $address
     * @param int|null $customerId
     * @return AddressInterface|QuoteAddressInterface|null
     */
    public function determineDestination($address = null, ?int $customerId = null)
    {
        if ($address !== null && !($address instanceof AddressInterface || $address instanceof QuoteAddressInterface)) {
            throw new \InvalidArgumentException(
                '$address must be a Customer or Quote Address.  Is: '
                // gettype() used for debug output and not for checking types
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                . (is_object($address) ? get_class($address) : gettype($address))
            );
        }

        if (!$this->isIncompleteAddress($address)) {
            return $address;
        }

        if (!$customerId) {
            // The address is incomplete and there's nothing to fall back to
            return null;
        }

        return $this->getDefaultShipping($customerId);
    }

    /**
     * Retrieve the default billing address for a customer
     */
    private function getDefaultBilling(int $customerId): ?AddressInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $addressId = $customer->getDefaultBilling();

            return $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            /* No-op */
            return null;
        } catch (\Exception $e) {
            $this->logger->warning($e);
            return null;
        }
    }

    /**
     * Retrieve the default shipping address for a customer
     */
    private function getDefaultShipping(int $customerId): ?AddressInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $addressId = $customer->getDefaultShipping();

            return $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            /* No-op */
            return null;
        } catch (\Exception $e) {
            $this->logger->warning($e);
            return null;
        }
    }

    /**
     * Determine whether or not the address is incomplete
     *
     * @param AddressInterface|QuoteAddressInterface $address
     */
    private function isIncompleteAddress($address): bool
    {
        if ($address instanceof AddressInterface) {
            return $this->incompleteAddressDeterminer->isIncompleteAddress($address);
        }
        if ($address instanceof QuoteAddressInterface) {
            return $this->incompleteAddressDeterminer->isIncompleteQuoteAddress($address);
        }
        return $address === null || $address->getCountryId() === null;
    }
}
