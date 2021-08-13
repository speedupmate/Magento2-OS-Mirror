<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Data\TaxRegistrationFactory;
use Vertex\Data\TaxRegistrationInterface;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\Api\Utility\MapperFactoryProxy;
use Vertex\Tax\Model\Data\VatCountryCode;

/**
 * Builds a TaxRegistration object for use with the Vertex SDK
 */
class TaxRegistrationBuilder
{
    /** @var TaxRegistrationFactory */
    private $taxRegistrationFactory;

    /** @var StringUtils */
    private $stringUtilities;

    /** @var MapperFactoryProxy */
    private $mapperFactory;

    /**
     * @param TaxRegistrationFactory $taxRegistrationFactory
     * @param StringUtils $stringUtils
     * @param MapperFactoryProxy $mapperFactory
     */
    public function __construct(
        TaxRegistrationFactory $taxRegistrationFactory,
        StringUtils $stringUtils,
        MapperFactoryProxy $mapperFactory
    ) {
        $this->taxRegistrationFactory = $taxRegistrationFactory;
        $this->stringUtilities = $stringUtils;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * Generate a VAT TaxRegistration from a Customer Address
     *
     * @param AddressInterface $address
     * @return TaxRegistration
     * @throws \InvalidArgumentException When address without VAT is specified
     * @throws ConfigurationException
     */
    public function buildFromCustomerAddress(AddressInterface $address)
    {
        if (!$address->getVatId()) {
            throw new \InvalidArgumentException('Address does not contain VAT');
        }

        $taxMapper = $this->mapperFactory->getForClass(TaxRegistrationInterface::class);

        /** @var TaxRegistration $registration */
        $registration = $this->taxRegistrationFactory->create();

        $registrationNumber = $this->stringUtilities->substr(
            $address->getVatId(),
            0,
            $taxMapper->getRegistrationNumberMaxLength()
        );
        $registration->setRegistrationNumber($registrationNumber)
            ->setImpositionType('VAT');

        if ($address->getCountryId()) {
            $countryCode = $this->stringUtilities->substr(
                $address->getCountryId(),
                0,
                $taxMapper->getCountryCodeMaxLength()
            );
            $registration->setCountryCode($countryCode);
        }

        return $registration;
    }

    /**
     * Generate a VAT TaxRegistration from an Order Address
     *
     * @param OrderAddressInterface $address
     * @param null|string $customerTaxvat
     * @return TaxRegistration
     * @throws \InvalidArgumentException When address without VAT is specified
     * @throws ConfigurationException
     */
    public function buildFromOrderAddress(OrderAddressInterface $address, $customerTaxvat = null)
    {
        $customerVatTaxCountry = $address->getExtensionAttributes()->getVertexVatCountryCode();

        if ($address->getVatId()) {
            $countryId = $address->getCountryId();
            $vatId = $address->getVatId();
        } elseif ($customerTaxvat && $customerVatTaxCountry) {
            $countryId = $customerVatTaxCountry;
            $vatId = $customerTaxvat;
        } else {
            return null;
        }

        $taxMapper = $this->mapperFactory->getForClass(TaxRegistrationInterface::class);

        /** @var TaxRegistration $registration */
        $registration = $this->taxRegistrationFactory->create();

        $registrationNumber = $this->stringUtilities->substr(
            $vatId,
            0,
            $taxMapper->getRegistrationNumberMaxLength()
        );
        $registration->setRegistrationNumber($registrationNumber)
            ->setImpositionType('VAT');

        if ($countryId) {
            $countryCode = $this->stringUtilities->substr(
                $countryId,
                0,
                $taxMapper->getCountryCodeMaxLength()
            );
            $registration->setCountryCode($countryCode);
        } else {
            return null;
        }

        return $registration;
    }

    /**
     * Generate a VAT TaxRegistration from a Customer
     *
     * @param CustomerInterface $customer
     * @return TaxRegistration
     * @throws \InvalidArgumentException When no customer is provided
     * @throws ConfigurationException
     */
    public function buildFromCustomer(CustomerInterface $customer)
    {
        $taxMapper = $this->mapperFactory->getForClass(TaxRegistrationInterface::class);

        /** @var TaxRegistration $registration */
        $registration = $this->taxRegistrationFactory->create();

        $registrationNumber = $this->stringUtilities->substr(
            $customer->getTaxvat(),
            0,
            $taxMapper->getRegistrationNumberMaxLength()
        );

        $registration->setRegistrationNumber($registrationNumber)
            ->setImpositionType('VAT');

        $extensionAttributes = $customer->getExtensionAttributes();

        if ($extensionAttributes->getVertexCustomerCountry()) {
            $countryCode = $this->stringUtilities->substr(
                $extensionAttributes->getVertexCustomerCountry(),
                0,
                $taxMapper->getCountryCodeMaxLength()
            );

            $registration->setCountryCode($countryCode);
        } else {
            return null;
        }

        return $registration;
    }
}
