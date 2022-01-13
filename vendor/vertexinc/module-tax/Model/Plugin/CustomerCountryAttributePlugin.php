<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CustomerCountry;
use Vertex\Tax\Model\Data\CustomerCountryFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\CustomerCountryRepository;

/**
 * Adds Customer Country extension attribute to Customer Repository
 *
 * @see CustomerRepositoryInterface
 */
class CustomerCountryAttributePlugin
{
    /** @var Config */
    private $config;

    /** @var CustomerCountryRepository */
    private $customerCountryRepository;

    /** @var CustomerCountryFactory */
    private $customerCountryFactory;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        Config $config,
        CustomerCountryRepository $customerCountryRepository,
        CustomerCountryFactory $customerCountryFactory,
        ExceptionLogger $logger
    ) {
        $this->config = $config;
        $this->customerCountryRepository = $customerCountryRepository;
        $this->customerCountryFactory = $customerCountryFactory;
        $this->logger = $logger;
    }

    /**
     * Delete the Vertex Customer Country when the customer is deleted
     *
     * @see CustomerRepositoryInterface::delete()
     * @param CustomerRepositoryInterface $subject
     * @param bool $result
     * @param CustomerInterface $customer
     * @return bool
     */
    public function afterDelete(
        CustomerRepositoryInterface $subject,
        $result,
        CustomerInterface $customer
    ): bool {
        if ($customer->getId() && $result) {
            $this->deleteByCustomerId($customer->getId());
        }

        return $result;
    }

    /**
     * Delete the Vertex Customer code when the customer is deleted
     *
     * @see CustomerRepositoryInterface::deleteById()
     * @param CustomerRepositoryInterface $subject
     * @param bool $result
     * @param int $customerId
     * @return bool
     */
    public function afterDeleteById(
        CustomerRepositoryInterface $subject,
        $result,
        $customerId
    ): bool {
        if ($result) {
            $this->deleteByCustomerId($customerId);
        }

        return $result;
    }

    /**
     * Add Customer Country to the Customer extension attribute when a customer is retrieved from the repository
     *
     * @see CustomerRepositoryInterface::get()
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $result): CustomerInterface
    {
        return $this->afterGetById($subject, $result);
    }

    /**
     * Add Customer Country to the Customer extension attribute when a customer is retrieved from the repository
     *
     * @see CustomerRepositoryInterface::getById()
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $result): CustomerInterface
    {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        $extensionAttributes = $result->getExtensionAttributes();

        try {
            $customerCountry = $this->customerCountryRepository->getByCustomerId($result->getId());
            $extensionAttributes->setVertexCustomerCountry($customerCountry->getCustomerCountry());
        } catch (NoSuchEntityException $exception) {
            $extensionAttributes->setVertexCustomerCountry(null);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Add Customer Country to the Customer extension attribute when customers are retrieved from the repository
     *
     * @see CustomerRepositoryInterface::getList()
     * @param CustomerRepositoryInterface $subject
     * @param CustomerSearchResultsInterface $results
     * @return CustomerSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(CustomerRepositoryInterface $subject, $results): CustomerSearchResultsInterface
    {
        if (!$this->config->isVertexActive() || $results->getTotalCount() <= 0) {
            return $results;
        }

        $customerIds = array_map(
            static function (CustomerInterface $customer) {
                return $customer->getId();
            },
            $results->getItems()
        );

        $customerCountries = $this->customerCountryRepository->getListByCustomerIds($customerIds);

        foreach ($results->getItems() as $customer) {
            if (!isset($customerCountries[$customer->getId()])) {
                continue;
            }

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setVertexCustomerCountry(
                $customerCountries[$customer->getId()]->getCustomerCountry()
            );
        }

        return $results;
    }

    /**
     * Saves Customer Country extension attribute
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result The customer entity result.
     * @param CustomerInterface $customer The customer entity with modified data, if any.
     * @return CustomerInterface
     * @throws InputException When there is taxvat but no Country specified.
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer
    ): CustomerInterface {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        if ($customer->getExtensionAttributes()) {
            $customerCountry = $customer->getExtensionAttributes()->getVertexCustomerCountry();

            if ($customer->getTaxvat() && !$customerCountry) {
                throw new InputException(__('Tax/VAT number provided but no Country was specified.'));
            }

            if ($customerCountry) {
                $customerCountryModel = $this->getCustomerCountryModel($result->getId());
                $customerCountryModel->setCustomerCountry($customerCountry);

                try {
                    $this->customerCountryRepository->save($customerCountryModel);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                $this->deleteByCustomerId($result->getId());
            }
        }

        return $result;
    }

    /**
     * Delete a Customer Country given a Customer ID
     *
     * @param int $customerId
     * @return void
     */
    private function deleteByCustomerId($customerId)
    {
        try {
            $this->customerCountryRepository->deleteByCustomerId($customerId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve the Customer Country by Customer ID
     *
     * @param int $customerId
     * @return CustomerCountry
     */
    private function getCustomerCountryModel($customerId): CustomerCountry
    {
        try {
            $customerCountry = $this->customerCountryRepository->getByCustomerId($customerId);
        } catch (NoSuchEntityException $e) {
            /** @var CustomerCountry $customerCountry */
            $customerCountry = $this->customerCountryFactory->create();
            $customerCountry->setCustomerId($customerId);
        }
        return $customerCountry;
    }
}
