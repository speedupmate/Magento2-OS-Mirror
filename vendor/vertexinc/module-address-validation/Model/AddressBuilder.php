<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model;

use Magento\Quote\Api\Data\AddressInterface as QuoteAddress;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Model\Data\AddressFactory;

class AddressBuilder
{
    /** @var AddressFactory */
    private $addressFactory;

    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    /**
     * Generate a Vertex SDK Address object based off a Magento Quote Address
     *
     * Scope is necessary for determining the Vertex SDK version to be used.  We
     * need to know the version so we can determine string length constraints.
     */
    public function buildFromQuoteAddress(QuoteAddress $quoteAddress): AddressInterface
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();

        if (!$this->validateQuoteAddressComplete($quoteAddress)) {
            return $address;
        }

        return $address->setStreetAddress($quoteAddress->getStreet())
            ->setCity($quoteAddress->getCity())
            ->setMainDivision($quoteAddress->getRegion())
            ->setPostalCode($quoteAddress->getPostcode())
            ->setCountry($quoteAddress->getCountryId());
    }

    private function validateQuoteAddressComplete(QuoteAddress $address): bool
    {
        return !empty($address->getStreet())
            && $address->getCity()
            && $address->getRegionId()
            && $address->getPostcode()
            && $address->getCountryId();
    }
}
