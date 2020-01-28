<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Address;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Data\AddressInterface as VertexAddressInterface;
use Vertex\Data\AddressInterfaceFactory;
use Vertex\Services\TaxAreaLookup\RequestInterfaceFactory;
use Vertex\Tax\Api\TaxAreaLookupInterface;
use Vertex\Tax\Model\Api\Data\AddressBuilder;
use Vertex\Tax\Model\ExceptionLogger;

class Builder implements BuilderInterface
{
    /** @var TaxAreaLookupInterface */
    private $taxAreaLookup;

    /** @var AddressBuilder */
    private $addressBuilder;

    /** @var RequestInterfaceFactory  */
    private $lookupRequestFactory;

    /** @var AddressInterfaceFactory */
    private $addressInterfaceFactory;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        TaxAreaLookupInterface $taxAreaLookup,
        AddressBuilder $addressBuilder,
        RequestInterfaceFactory $lookupRequestFactory,
        AddressInterfaceFactory $addressInterfaceFactory,
        ExceptionLogger $logger
    ) {
        $this->taxAreaLookup = $taxAreaLookup;
        $this->addressBuilder = $addressBuilder;
        $this->lookupRequestFactory = $lookupRequestFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->logger = $logger;
    }

    public function execute(
        AddressInterface $address,
        int $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): VertexAddressInterface {
        $addressInterfaceFactory = $this->addressInterfaceFactory->create();

        if (!$this->validateQuoteAddressComplete($address)) {
            return $addressInterfaceFactory;
        }

        $request = $this->lookupRequestFactory->create();
        $convertedAddress = $this->convertedToVertexAddress($address, $scopeCode);

        if (!$this->validateVertexAddress($convertedAddress)) {
            return $addressInterfaceFactory;
        }

        $request->setPostalAddress($convertedAddress);

        try {
            $taxAreaLookup = $this->taxAreaLookup->lookup($request, $scopeCode, $scopeType);
            $result = $taxAreaLookup->getResults();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $addresses = [];
        if (!empty($result) && isset($result[0])) {
            $addresses = $result[0]->getPostalAddresses();
        }

        if (empty($addresses) && !isset($addresses[0])) {
            return $addressInterfaceFactory;
        }

        return $addresses[0];
    }

    private function convertedToVertexAddress(AddressInterface $address, int $scopeCode): VertexAddressInterface
    {
        $vertexAddress = $this->addressBuilder
            ->setScopeCode($scopeCode)
            ->setStreet($address->getStreet())
            ->setCity($address->getCity())
            ->setRegionId($address->getRegionId())
            ->setPostalCode($address->getPostcode())
            ->setCountryCode($address->getCountryId())
            ->build();

        return $vertexAddress;
    }

    private function validateQuoteAddressComplete(AddressInterface $address): bool
    {
        return !empty($address->getStreet())
            && $address->getCity()
            && $address->getRegionId()
            && $address->getPostcode()
            && $address->getCountryId();
    }

    private function validateVertexAddress(VertexAddressInterface $convertedAddress): bool
    {
        return  !empty($convertedAddress->getStreetAddress())
            && $convertedAddress->getCity()
            && $convertedAddress->getPostalCode()
            && $convertedAddress->getCountry();
    }
}
