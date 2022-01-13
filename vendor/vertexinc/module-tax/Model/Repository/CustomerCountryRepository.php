<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Repository;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vertex\Tax\Model\Data\CustomerCountry;
use Vertex\Tax\Model\Data\CustomerCountryFactory;
use Vertex\Tax\Model\Registry\CustomerCountryRegistry;
use Vertex\Tax\Model\ResourceModel\CustomerCountry as ResourceModel;
use Vertex\Tax\Model\ResourceModel\CustomerCountry\CollectionFactory;

/**
 * Repository of Vertex Customer Country
 */
class CustomerCountryRepository
{
    /** @var CustomerCountryRegistry */
    private $registry;

    /** @var ResourceModel */
    private $resourceModel;

    /** @var CustomerCountryFactory */
    private $factory;

    public function __construct(
        CustomerCountryRegistry $registry,
        ResourceModel $resourceModel,
        CustomerCountryFactory $factory
    ) {
        $this->registry = $registry;
        $this->resourceModel = $resourceModel;
        $this->factory = $factory;
    }

    /**
     * Delete a Customer Country
     *
     * @param CustomerCountry $customerCountry
     * @return $this
     * @throws CouldNotDeleteException
     */
    public function delete(CustomerCountry $customerCountry)
    {
        try {
            $customerId = $customerCountry->getCustomerId();
            $this->resourceModel->delete($customerCountry);
            $this->registry->delete($customerId);
        } catch (\Exception $originalException) {
            throw new CouldNotDeleteException(__('Unable to delete Customer Country'), $originalException);
        }

        return $this;
    }

    /**
     * Delete a Customer Country given a Customer ID
     *
     * @param int $customerId
     * @return $this
     * @throws CouldNotDeleteException
     */
    public function deleteByCustomerId($customerId)
    {
        /** @var CustomerCountry $customerCountry */
        $customerCountry = $this->factory->create();
        $customerCountry->setId($customerId);

        return $this->delete($customerCountry);
    }

    /**
     * Retrieve a Customer Code given a Customer ID
     *
     * @param int $customerId
     * @return CustomerCountry
     * @throws NoSuchEntityException
     */
    public function getByCustomerId($customerId)
    {
        /** @var CustomerCountry $customerCountry */
        $customerCountry = $this->factory->create();

        $registered = $this->registry->get($customerId);
        if ($registered === null) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        if ($registered !== false) {
            $customerCountry->setCustomerId($customerId);
            $customerCountry->setCustomerCountry($registered);
            return $customerCountry;
        }

        $this->resourceModel->load($customerCountry, $customerId);
        if (!$customerCountry->getId()) {
            $this->registry->set($customerId, null);
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        $this->registry->set($customerCountry->getCustomerId(), $customerCountry->getCustomerCountry());
        return $customerCountry;
    }

    /**
     * Retrieve an array of Customer Country's indexed by Customer ID
     *
     * @param int[] $customerIds
     * @return CustomerCountry[] Indexed by Customer ID
     */
    public function getListByCustomerIds(array $customerIds): array
    {
        $unregisteredIds = [];
        $registryCustomerCountries = [];
        foreach ($customerIds as $customerId) {
            $registered = $this->registry->get($customerId);
            if ($registered === false) {
                $unregisteredIds[] = $customerId;
            } else {
                $customerCountry = $this->factory->create();
                $customerCountry->setCustomerId($customerId);
                $customerCountry->setCustomerCountry($registered);
                $registryCustomerCountries[$customerId] = $customerCountry;
            }
        }

        $dbCustomerCountries = [];
        if (!empty($unregisteredIds)) {
            $dbCustomerCountries = $this->resourceModel->getArrayByCustomerIds($unregisteredIds);
        }

        foreach ($dbCustomerCountries as $dbCustomerCountry) {
            $this->registry->set($dbCustomerCountry->getCustomerId(), $dbCustomerCountry->getCustomerCountry());
        }

        return array_replace($dbCustomerCountries, $registryCustomerCountries);
    }

    /**
     * Save a Customer Country
     *
     * @param CustomerCountry $customerCountry
     * @return $this
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(CustomerCountry $customerCountry): CustomerCountryRepository
    {
        $registered = $this->registry->get($customerCountry->getCustomerId());
        if ($registered === $customerCountry->getCustomerCountry()) {
            return $this;
        }

        try {
            $this->resourceModel->save($customerCountry);
            $this->registry->set($customerCountry->getCustomerId(), $customerCountry->getCustomerCountry());
        } catch (AlreadyExistsException $e) {
            throw $e;
        } catch (\Exception $originalException) {
            throw new CouldNotSaveException(__('Unable to save Customer Country'), $originalException);
        }
        return $this;
    }
}
