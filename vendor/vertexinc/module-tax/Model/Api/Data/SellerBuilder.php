<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Framework\Stdlib\StringUtils;
use Vertex\Data\SellerInterface;
use Vertex\Data\SellerInterfaceFactory;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Config;

/**
 * Create a {@see SellerInterface} from store configuration
 */
class SellerBuilder
{
    /** @var AddressBuilder */
    private $addressBuilder;

    /** @var Config */
    private $config;

    /** @var SellerInterfaceFactory */
    private $sellerFactory;

    /** @var string */
    private $scopeCode;

    /** @var string */
    private $scopeType;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /** @var StringUtils */
    private $stringUtilities;

    /**
     * @param SellerInterfaceFactory $sellerFactory
     * @param Config $config
     * @param AddressBuilder $addressBuilder
     * @param MapperFactoryProxy $mapperFactory
     * @param StringUtils $stringUtils
     */
    public function __construct(
        SellerInterfaceFactory $sellerFactory,
        Config $config,
        AddressBuilder $addressBuilder,
        MapperFactoryProxy $mapperFactory,
        StringUtils $stringUtils
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->config = $config;
        $this->addressBuilder = $addressBuilder;
        $this->mapperFactory = $mapperFactory;
        $this->stringUtilities = $stringUtils;
    }

    /**
     * Create a {@see SellerInterface} from store configuration
     *
     * @return SellerInterface
     * @throws ConfigurationException
     */
    public function build()
    {
        /** @var SellerInterface $seller */
        $seller = $this->sellerFactory->create();
        $sellerMapper = $this->mapperFactory->getForClass(SellerInterface::class, $this->scopeCode, $this->scopeType);

        $street = [
            $this->config->getCompanyStreet1($this->scopeCode, $this->scopeType),
            $this->config->getCompanyStreet2($this->scopeCode, $this->scopeType)
        ];

        $address = $this->addressBuilder
            ->setScopeCode($this->scopeCode)
            ->setScopeType($this->scopeType)
            ->setStreet($street)
            ->setCity($this->config->getCompanyCity($this->scopeCode, $this->scopeType))
            ->setRegionId($this->config->getCompanyRegionId($this->scopeCode, $this->scopeType))
            ->setPostalCode($this->config->getCompanyPostalCode($this->scopeCode, $this->scopeType))
            ->setCountryCode($this->config->getCompanyCountry($this->scopeCode, $this->scopeType))
            ->build();

        $seller->setPhysicalOrigin($address);

        $configCompanyCode = $this->config->getCompanyCode($this->scopeCode, $this->scopeType);

        if ($configCompanyCode) {
            $companyCode = $this->stringUtilities->substr(
                $configCompanyCode,
                0,
                $sellerMapper->getCompanyCodeMaxLength()
            );

            $seller->setCompanyCode($companyCode);
        }

        return $seller;
    }

    /**
     * Set the Scope Code
     *
     * @param string|null $scopeCode
     * @return SellerBuilder
     */
    public function setScopeCode($scopeCode)
    {
        $this->scopeCode = $scopeCode;
        return $this;
    }

    /**
     * Set the Scope Type
     *
     * @param string|null $scopeType
     * @return SellerBuilder
     */
    public function setScopeType($scopeType)
    {
        $this->scopeType = $scopeType;
        return $this;
    }
}
