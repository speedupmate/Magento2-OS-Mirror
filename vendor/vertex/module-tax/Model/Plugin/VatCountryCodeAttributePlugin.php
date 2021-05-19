<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Vertex\Tax\Model\Config;
use Vertex\Tax\Model\Data\VatCountryCode;
use Vertex\Tax\Model\Data\VatCountryCodeFactory;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\VatCountryCodeRepository;

/**
 * Adds Vat Country Code extension attribute to Order Address repository
 *
 * @see OrderAddressRepositoryInterface
 */
class VatCountryCodeAttributePlugin
{
    /** @var Config */
    private $config;

    /** @var VatCountryCodeRepository */
    private $vatCountryCodeRepository;

    /** @var VatCountryCodeFactory */
    private $vatCountryCodeFactory;

    /** @var ExceptionLogger */
    private $logger;

    /** @var bool[] */
    private $currentlySaving = [];

    public function __construct(
        Config $config,
        VatCountryCodeRepository $vatCountryCodeRepository,
        VatCountryCodeFactory $vatCountryCodeFactory,
        ExceptionLogger $logger
    ) {
        $this->config = $config;
        $this->vatCountryCodeRepository = $vatCountryCodeRepository;
        $this->vatCountryCodeFactory = $vatCountryCodeFactory;
        $this->logger = $logger;
    }

    /**
     * Delete the Vat Country Code when the order address is deleted
     *
     * @see OrderAddressRepositoryInterface::delete()
     * @param OrderAddressRepositoryInterface $subject
     * @param bool $result
     * @param OrderAddressInterface $orderAddress
     * @return bool
     */
    public function afterDelete(
        OrderAddressRepositoryInterface $subject,
        $result,
        OrderAddressInterface $orderAddress
    ): bool {
        if ($orderAddress->getId() && $this->config->isVertexActive() && $result) {
            $this->deleteByAddressId($orderAddress->getId());
        }

        return $result;
    }

    /**
     * Delete the Vat Country Code when the Order Address is deleted
     *
     * @see OrderAddressRepositoryInterface::deleteById()
     * @param OrderAddressInterface $subject
     * @param bool $result
     * @param int $addressId
     * @return bool
     */
    public function afterDeleteById(
        OrderAddressInterface $subject,
        $result,
        $addressId
    ): bool {
        if ($this->config->isVertexActive() && $result) {
            $this->deleteByAddressId($addressId);
        }

        return $result;
    }

    /**
     * Add Vat Country Code to the Order Address extension attribute when an Order Address is retrieved
     *
     * @see OrderAddressRepositoryInterface::get()
     * @param OrderAddressRepositoryInterface $subject
     * @param OrderAddressInterface $result
     * @return OrderAddressInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderAddressRepositoryInterface $subject,
        OrderAddressInterface $result
    ): OrderAddressInterface {
        if (!$this->config->isVertexActive()) {
            return $result;
        }

        try {
            $vatCountryCode = $this->vatCountryCodeRepository->getByAddressId($result->getEntityId());
            $result->getExtensionAttributes()->setVertexVatCountryCode($vatCountryCode->getVatCountryCode());
        } catch (NoSuchEntityException $exception) {
            $result->getExtensionAttributes()->setVertexVatCountryCode(null);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }

    /**
     * Add Vat Country Code to the Order Address extension attribute when an Order Address is retrieved
     *
     * @see OrderAddressRepositoryInterface::getList()
     * @param OrderAddressRepositoryInterface $subject
     * @param OrderAddressSearchResultInterface $results
     * @return OrderAddressSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(OrderAddressRepositoryInterface $subject, $results): OrderAddressSearchResultInterface
    {
        if (!$this->config->isVertexActive() || $results->getTotalCount() <= 0) {
            return $results;
        }

        $addressIds = array_map(
            static function (OrderAddressInterface $orderAddress) {
                return $orderAddress->getEntityId();
            },
            $results->getItems()
        );

        $vatCountryCodes = $this->vatCountryCodeRepository->getListByAddressIds($addressIds);

        foreach ($results->getItems() as $orderAddress) {
            if (!isset($vatCountryCodes[$orderAddress->getEntityId()])) {
                continue;
            }

            $extensionAttributes = $orderAddress->getExtensionAttributes();
            $extensionAttributes->setVertexVatCountryCode(
                $vatCountryCodes[$orderAddress->getEntityId()]->getVatCountryCode()
            );
        }

        return $results;
    }

    /**
     * Saves Vat Country Code extension attribute
     *
     * @see OrderAddressRepositoryInterface::save()
     * @param OrderAddressRepositoryInterface $subject
     * @param OrderAddressInterface $result The order address entity result.
     * @param OrderAddressInterface $orderAddress The order address entity with modified data, if any.
     * @return OrderAddressInterface
     */
    public function afterSave(
        OrderAddressRepositoryInterface $subject,
        OrderAddressInterface $result,
        OrderAddressInterface $orderAddress
    ): OrderAddressInterface {
        if (!$this->config->isVertexActive($result->getStoreId())) {
            return $result;
        }

        if ($orderAddress->getExtensionAttributes()) {
            $vatCountryCode = $orderAddress->getExtensionAttributes()->getVertexVatCountryCode();

            if ($vatCountryCode) {
                $vatCountryCodeModel = $this->getVatCountryCodeModel($result->getEntityId());
                $vatCountryCodeModel->setVatCountryCode($vatCountryCode);

                try {
                    $this->vatCountryCodeRepository->save($vatCountryCodeModel);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                $this->deleteByAddressId($result->getEntityId());
            }
        }

        return $result;
    }

    /**
     * Delete a Vat Country Code given an Address ID
     *
     * @param int $addressId
     * @return void
     */
    private function deleteByAddressId($addressId)
    {
        try {
            $this->vatCountryCodeRepository->deleteByAddressId($addressId);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Retrieve the Vat Country Code by Address
     *
     * @param int $addressId
     * @return VatCountryCode
     */
    private function getVatCountryCodeModel($addressId): VatCountryCode
    {
        try {
            $vatCountryCode = $this->vatCountryCodeRepository->getByAddressId($addressId);
        } catch (NoSuchEntityException $e) {
            /** @var VatCountryCode $vatCountryCode */
            $vatCountryCode = $this->vatCountryCodeFactory->create();
            $vatCountryCode->setAddressId($addressId);
        }
        return $vatCountryCode;
    }
}
