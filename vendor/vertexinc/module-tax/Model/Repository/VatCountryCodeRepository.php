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
use Vertex\Tax\Model\Data\VatCountryCode;
use Vertex\Tax\Model\Data\VatCountryCodeFactory;
use Vertex\Tax\Model\Registry\VatCountryCodeRegistry;
use Vertex\Tax\Model\ResourceModel\VatCountryCode as ResourceModel;
use Vertex\Tax\Model\ResourceModel\VatCountryCode\CollectionFactory;

/**
 * Repository of Vertex Vat Country Code
 */
class VatCountryCodeRepository
{
    /** @var VatCountryCodeRegistry */
    private $registry;

    /** @var ResourceModel */
    private $resourceModel;

    /** @var VatCountryCodeFactory */
    private $factory;

    public function __construct(
        VatCountryCodeRegistry $registry,
        ResourceModel $resourceModel,
        VatCountryCodeFactory $factory
    ) {
        $this->registry = $registry;
        $this->resourceModel = $resourceModel;
        $this->factory = $factory;
    }

    /**
     * Delete a Vat Country Code
     *
     * @param VatCountryCode $vatCountryCode
     * @return $this
     * @throws CouldNotDeleteException
     */
    public function delete(VatCountryCode $vatCountryCode)
    {
        try {
            $addressId = $vatCountryCode->getAddressId();
            $this->resourceModel->delete($vatCountryCode);
            $this->registry->delete($addressId);
        } catch (\Exception $originalException) {
            throw new CouldNotDeleteException(__('Unable to delete Vat Country Code'), $originalException);
        }

        return $this;
    }

    /**
     * Delete a Vat Country Code given an Address ID
     *
     * @param int $addressId
     * @return $this
     * @throws CouldNotDeleteException
     */
    public function deleteByAddressId($addressId)
    {
        /** @var VatCountryCode $vatCountryCode */
        $vatCountryCode = $this->factory->create();
        $vatCountryCode->setId($addressId);

        return $this->delete($vatCountryCode);
    }

    /**
     * Retrieve a Vat Country Code given an Address ID
     *
     * @param int $addressId
     * @return VatCountryCode
     * @throws NoSuchEntityException
     */
    public function getByAddressId($addressId): VatCountryCode
    {
        /** @var VatCountryCode $vatCountryCode */
        $vatCountryCode = $this->factory->create();

        $registered = $this->registry->get($addressId);
        if ($registered === null) {
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }
        if ($registered !== false) {
            $vatCountryCode->setAddressId($addressId);
            $vatCountryCode->setVatCountryCode($registered);
            return $vatCountryCode;
        }

        $this->resourceModel->load($vatCountryCode, $addressId);
        if (!$vatCountryCode->getId()) {
            $this->registry->set($addressId, null);
            throw NoSuchEntityException::singleField('addressId', $addressId);
        }
        $this->registry->set($vatCountryCode->getAddressId(), $vatCountryCode->getVatCountryCode());
        return $vatCountryCode;
    }

    /**
     * Retrieve an array of Vat Country Codes indexed by Address ID
     *
     * @param int[] $addressIds
     * @return VatCountryCode[] Indexed by Address ID
     */
    public function getListByAddressIds(array $addressIds): array
    {
        $unregisteredIds = [];
        $registryVatCountryCodes = [];
        foreach ($addressIds as $addressId) {
            $registered = $this->registry->get($addressId);
            if ($registered === false) {
                $unregisteredIds[] = $addressId;
            } else {
                /** @var VatCountryCode $vatCountryCode */
                $vatCountryCode = $this->factory->create();
                $vatCountryCode->setAddressId($addressId);
                $vatCountryCode->setVatCountryCode($registered);
                $registryVatCountryCodes[$addressId] = $vatCountryCode;
            }
        }

        $dbVatCountryCodes = [];
        if (!empty($unregisteredIds)) {
            $dbVatCountryCodes = $this->resourceModel->getArrayByAddressId($unregisteredIds);
        }

        foreach ($dbVatCountryCodes as $dbVatCountryCode) {
            $this->registry->set($dbVatCountryCode->getAddressId(), $dbVatCountryCode->getVatCountryCode());
        }

        return array_replace($dbVatCountryCodes, $registryVatCountryCodes);
    }

    /**
     * Save a Vat Country Code
     *
     * @param VatCountryCode $vatCountryCode
     * @return $this
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(VatCountryCode $vatCountryCode): VatCountryCodeRepository
    {
        $registered = $this->registry->get($vatCountryCode->getAddressId());
        if ($registered === $vatCountryCode->getVatCountryCode()) {
            return $this;
        }

        try {
            $this->resourceModel->save($vatCountryCode);
            $this->registry->set($vatCountryCode->getAddressId(), $vatCountryCode->getVatCountryCode());
        } catch (AlreadyExistsException $e) {
            throw $e;
        } catch (\Exception $originalException) {
            throw new CouldNotSaveException(__('Unable to save Vat Country Code'), $originalException);
        }
        return $this;
    }
}
