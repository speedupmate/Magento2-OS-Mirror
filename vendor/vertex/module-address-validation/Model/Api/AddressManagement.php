<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Api;

use Magento\Framework\Webapi\Exception;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\AddressValidation\Model\Address\BuilderInterface;
use Vertex\AddressValidation\Model\Address\BuilderInterfaceFactory;
use Vertex\AddressValidation\Model\Config;
use Vertex\AddressValidation\Api\AddressManagementInterface;

class AddressManagement implements AddressManagementInterface
{
    /** @var BuilderInterface */
    private $builderFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Config */
    private $config;

    public function __construct(
        BuilderInterfaceFactory $builderFactory,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->builderFactory = $builderFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    public function getValidAddress(AddressInterface $address): AddressInterface
    {
        if (!$this->config->isAddressValidationEnabled()) {
            throw new Exception(
                __('Request does not match any route.'),
                0,
                Exception::HTTP_NOT_FOUND
            );
        }

        $storeId = (int) $this->storeManager->getStore()->getId();

        /** @var BuilderInterface $builder */
        $builder = $this->builderFactory->create();
        $vertexAddress = $builder->execute($address, $storeId);

        $address->setCity($vertexAddress->getCity());
        $address->setPostcode($vertexAddress->getPostalCode());
        $address->setStreet($vertexAddress->getStreetAddress());

        return $address;
    }
}
