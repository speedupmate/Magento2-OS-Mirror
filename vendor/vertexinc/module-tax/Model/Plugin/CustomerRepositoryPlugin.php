<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\CustomerCode;
use Vertex\Tax\Model\Data\CustomerCodeFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\CustomerCodeRepository;

/**
 * Adds CustomerCode extension attribute to Customer Repository
 *
 * @see CustomerRepositoryInterface
 */
class CustomerRepositoryPlugin
{
    /** @var CustomerCodeFactory */
    private $codeFactory;

    /** @var Config */
    private $config;

    /** @var bool[] */
    private $currentlySaving = [];

    /** @var ExceptionLogger */
    private $logger;

    /** @var CustomerCodeRepository */
    private $repository;

    public function __construct(
        CustomerCodeRepository $repository,
        CustomerCodeFactory $codeFactory,
        ExceptionLogger $logger,
        Config $config
    ) {
        $this->repository = $repository;
        $this->codeFactory = $codeFactory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Delete the Vertex Customer Code when the customer is deleted
     *
     * @param CustomerRepositoryInterface $subject
     * @param bool $result
     * @param CustomerInterface $customer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::delete()
     */
    public function afterDelete(CustomerRepositoryInterface $subject, $result, CustomerInterface $customer): bool
    {
        $customerId = $customer->getId();

        if ($result && $this->config->isVertexActive()) {
            $this->deleteByCustomerId($customerId);
        }

        return (bool)$result;
    }

    /**
     * Delete the Vertex Customer code when the customer is deleted
     *
     * @param CustomerRepositoryInterface $subject
     * @param bool $result
     * @param int $customerId
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::deleteById()
     */
    public function afterDeleteById(CustomerRepositoryInterface $subject, $result, $customerId): bool
    {
        if ($result && $this->config->isVertexActive()) {
            $this->deleteByCustomerId($customerId);
        }

        return (bool)$result;
    }

    /**
     * Add the Vertex Customer Code to the Customer extension attribute when a customer is retrieved from the repository
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::get()
     */
    public function afterGet(CustomerRepositoryInterface $subject, $result)
    {
        return $this->afterGetById($subject, $result);
    }

    /**
     * Add the Vertex Customer Code to the Customer extension attribute when a customer is retrieved from the repository
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::getById()
     */
    public function afterGetById(CustomerRepositoryInterface $subject, $result)
    {
        if (!$this->config->isVertexActive($result->getStoreId()) || $this->isCurrentlySaving($result)) {
            return $result;
        }

        $extensionAttributes = $result->getExtensionAttributes();

        try {
            $customerCode = $this->repository->getByCustomerId($result->getId());
            $extensionAttributes->setVertexCustomerCode($customerCode->getCustomerCode());
        } catch (NoSuchEntityException $exception) {
            $extensionAttributes->setVertexCustomerCode(null);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Add the Vertex Customer Code to the Customer extension attribute when customers are retrieved from the repository
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerSearchResultsInterface $results
     * @return CustomerSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::getList()
     */
    public function afterGetList(CustomerRepositoryInterface $subject, $results)
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

        $customerCodes = $this->repository->getListByCustomerIds($customerIds);

        foreach ($results->getItems() as $customer) {
            if (!isset($customerCodes[$customer->getId()])) {
                continue;
            }

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setVertexCustomerCode($customerCodes[$customer->getId()]->getCustomerCode());
        }

        return $results;
    }

    /**
     * Save the Vertex Customer Code when the Customer is saved
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $savedCustomer
     * @param CustomerInterface $toSaveCustomer
     * @return CustomerInterface $savedCustomer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see CustomerRepositoryInterface::save()
     *
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        $savedCustomer,
        $toSaveCustomer
    ) {
        if (!$this->config->isVertexActive($toSaveCustomer->getStoreId())) {
            return $savedCustomer;
        }
        $this->setCurrentlySaving($toSaveCustomer);
        if ($toSaveCustomer->getExtensionAttributes()) {
            $customerCode = $toSaveCustomer->getExtensionAttributes()->getVertexCustomerCode();

            if ($customerCode) {
                $codeModel = $this->getCodeModel($savedCustomer->getId());
                $codeModel->setCustomerCode($customerCode);
                try {
                    $this->repository->save($codeModel);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
                $extensionAttributes = $savedCustomer->getExtensionAttributes();
                $extensionAttributes->setVertexCustomerCode($customerCode);
            } else {
                $this->deleteByCustomerId($savedCustomer->getId());
            }
        }
        $this->unsetCurrentlySaving($toSaveCustomer);
        return $savedCustomer;
    }

    /**
     * Delete a Customer Code given a Customer ID
     *
     * @param int $customerId
     */
    private function deleteByCustomerId($customerId): void
    {
        try {
            $this->repository->deleteByCustomerId($customerId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve the Customer Code by Customer ID
     *
     * @param int $customerId
     */
    private function getCodeModel($customerId): CustomerCode
    {
        try {
            $customerCode = $this->repository->getByCustomerId($customerId);
        } catch (NoSuchEntityException $e) {
            /** @var CustomerCode $customerCode */
            $customerCode = $this->codeFactory->create();
            $customerCode->setCustomerId($customerId);
        }
        return $customerCode;
    }

    /**
     * Determine whether or not we are currently saving a specific customer
     *
     * This is used to prevent loading the attribute during a save procedure
     *
     * @param CustomerInterface $customer
     */
    private function isCurrentlySaving($customer): bool
    {
        return isset($this->currentlySaving[$customer->getId()]);
    }

    /**
     * Set whether or not we are currently saving a specific customer
     *
     * This is used to prevent loading the attribute during a save procedure
     *
     * @param CustomerInterface $customer
     */
    private function setCurrentlySaving($customer): void
    {
        if ($customer->getId()) {
            $this->currentlySaving[$customer->getId()] = true;
        }
    }

    /**
     * Declare that we are no longer currently saving a specific customer
     *
     * @param CustomerInterface $customer
     */
    private function unsetCurrentlySaving($customer): void
    {
        unset($this->currentlySaving[$customer->getId()]);
    }
}
