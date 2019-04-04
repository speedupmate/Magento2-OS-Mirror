<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Data;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Vertex\Data\TaxRegistration;
use Vertex\Data\TaxRegistrationFactory;

/**
 * Builds a TaxRegistration object for use with the Vertex SDK
 */
class TaxRegistrationBuilder
{
    /** @var TaxRegistrationFactory */
    private $taxRegistrationFactory;

    /**
     * @param TaxRegistrationFactory $taxRegistrationFactory
     */
    public function __construct(TaxRegistrationFactory $taxRegistrationFactory)
    {
        $this->taxRegistrationFactory = $taxRegistrationFactory;
    }

    /**
     * Generate a VAT TaxRegistration from a Customer Address
     *
     * @param AddressInterface $address
     * @return TaxRegistration
     * @throws \InvalidArgumentException When address without VAT is specified
     */
    public function buildFromCustomerAddress(AddressInterface $address)
    {
        if (!$address->getVatId()) {
            throw new \InvalidArgumentException('Address does not contain VAT');
        }

        /** @var TaxRegistration $registration */
        $registration = $this->taxRegistrationFactory->create();
        $registration->setRegistrationNumber($address->getVatId())
            ->setImpositionType('VAT');

        if ($address->getCountryId()) {
            $registration->setCountryCode($address->getCountryId());
        }

        return $registration;
    }

    /**
     * Generate a VAT TaxRegistration from an Order Address
     *
     * @param OrderAddressInterface $address
     * @return TaxRegistration
     * @throws \InvalidArgumentException When address without VAT is specified
     */
    public function buildFromOrderAddress(OrderAddressInterface $address)
    {
        if (!$address->getVatId()) {
            throw new \InvalidArgumentException('Address does not contain VAT');
        }

        /** @var TaxRegistration $registration */
        $registration = $this->taxRegistrationFactory->create();
        $registration->setRegistrationNumber($address->getVatId())
            ->setImpositionType('VAT');

        if ($address->getCountryId()) {
            $registration->setCountryCode($address->getCountryId());
        }

        return $registration;
    }
}
