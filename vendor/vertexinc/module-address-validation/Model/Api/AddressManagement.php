<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Api;

use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\AddressValidation\Api\AddressManagementInterface;
use Vertex\AddressValidation\Model\AddressBuilderFactory;
use Vertex\AddressValidation\Model\Config;
use Vertex\AddressValidationApi\Api\CleanseAddressInterface;

/**
 * @deprecated Unsecured API
 * @see CleanseAddressInterface
 */
class AddressManagement implements AddressManagementInterface
{
    /** @var AddressCleanser */
    private $addressCleanser;

    /** @var AddressBuilderFactory */
    private $builderFactory;

    /** @var Config */
    private $config;

    /** @var AddressInterfaceFactory */
    private $quoteAddressFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    public function __construct(
        AddressBuilderFactory $builderFactory,
        StoreManagerInterface $storeManager,
        Config $config,
        AddressCleanser $addressCleanser,
        AddressInterfaceFactory $quoteAddressFactory
    ) {
        $this->builderFactory = $builderFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->addressCleanser = $addressCleanser;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    public function getValidAddress(AddressInterface $address): AddressInterface
    {
        if (!$this->config->isAddressValidationEnabled() || !$this->config->isLegacyWebApiEnabled()) {
            throw new WebapiException(
                __('Request does not match any route.'),
                0,
                WebapiException::HTTP_NOT_FOUND
            );
        }

        $storeId = $this->storeManager->getStore()->getId();

        $builder = $this->builderFactory->create();
        $vertexAddress = $builder->buildFromQuoteAddress($address, (string)$storeId);

        $cleansedAddress = $this->addressCleanser->cleanseAddress(
            $vertexAddress,
            (string)$storeId,
            ScopeInterface::SCOPE_STORE
        );

        $quoteAddress = $this->quoteAddressFactory->create();
        if ($cleansedAddress === null) {
            return $quoteAddress;
        }

        $quoteAddress->setCity($cleansedAddress->getCity())
            ->setStreet($cleansedAddress->getStreetAddress())
            ->setRegionId($cleansedAddress->getRegionId())
            ->setRegion($cleansedAddress->getRegionName())
            ->setPostcode($cleansedAddress->getPostalCode());

        return $quoteAddress;
    }
}
