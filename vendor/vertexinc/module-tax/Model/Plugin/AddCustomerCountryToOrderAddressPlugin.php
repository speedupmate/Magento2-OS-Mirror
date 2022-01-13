<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\VatCountryCode;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Include the Vertex Customer Country to the order address in case
 *
 * @see OrderInterface
 */
class AddCustomerCountryToOrderAddressPlugin
{
    /** @var Config */
    private $config;

    /** @var ExceptionLogger */
    private $logger;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var OrderAddressRepositoryInterface */
    private $orderAddressRepository;

    public function __construct(
        Config $config,
        ExceptionLogger $logger,
        CustomerRepositoryInterface $customerRepository,
        OrderAddressRepositoryInterface $orderAddressRepository
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->orderAddressRepository = $orderAddressRepository;
    }

    /**
     * Save the customer country to the order address when no address VAT ID is present
     *
     * @see OrderInterface::save()
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $result
    ): OrderInterface {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        if ($result->getCustomerId()) {
            /** @var OrderAddressInterface $shippingAddress */
            $shippingAddress = $result->getShippingAddress();

            /** @var OrderAddressInterface $shippingAddress */
            $billingAddress = $result->getBillingAddress();

            $customerCountry = $this->getCustomerCountryById((int) $result->getCustomerId());

            if ($customerCountry) {
                $this->saveOrderAddressCountry($shippingAddress, $customerCountry);
                $this->saveOrderAddressCountry($billingAddress, $customerCountry);
            }
        }

        return $result;
    }

    /**
     * Load the Customer VAT country
     *
     * @param int $customerId
     * @return string|null
     */
    private function getCustomerCountryById(int $customerId): ?string
    {
        $country = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
            $extensionAttributes = $customer->getExtensionAttributes();
            if ($extensionAttributes->getVertexCustomerCountry()) {
                $country = $extensionAttributes->getVertexCustomerCountry();
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $country;
    }

    /**
     * Save the Vertex Vat Country Code to the Order Address entity
     *
     * @param OrderAddressInterface $address
     * @param string $customerCountry
     */
    private function saveOrderAddressCountry($address, $customerCountry)
    {
        if ($address->getEntityId() && !$address->getVatId()) {
            if (method_exists($address, 'setData')) {
                $address->setData(VatCountryCode::EXTENSION_ATTRIBUTE_CODE, $customerCountry);
            }

            $address->getExtensionAttributes()->setVertexVatCountryCode($customerCountry);
            $this->orderAddressRepository->save($address);
        }
    }
}
