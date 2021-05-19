<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Api;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Store\Model\ScopeInterface;
use Vertex\AddressValidation\Model\Config;
use Vertex\AddressValidationApi\Api\CleanseAddressInterface;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface;
use Vertex\AddressValidationApi\Model\Data\CleansedAddressFactory;
use Vertex\Exception\ApiException;
use Vertex\Exception\ConfigurationException;
use Vertex\Exception\ValidationException;
use Vertex\Services\TaxAreaLookup\RequestInterfaceFactory;
use Vertex\Tax\Api\TaxAreaLookupInterface;
use Vertex\Tax\Model\ExceptionLogger;

class AddressCleanser implements CleanseAddressInterface
{
    /** @var CleansedAddressFactory */
    private $cleansedAddressFactory;

    /** @var Config */
    private $config;

    /** @var CollectionFactory */
    private $countryCollectionFactory;

    /** @var ExceptionLogger */
    private $logger;

    /** @var TaxAreaLookupInterface */
    private $lookupService;

    /** @var RequestInterfaceFactory */
    private $requestFactory;

    public function __construct(
        Config $config,
        TaxAreaLookupInterface $lookupService,
        ExceptionLogger $logger,
        RequestInterfaceFactory $requestFactory,
        CleansedAddressFactory $cleansedAddressFactory,
        CollectionFactory $countryCollectionFactory
    ) {
        $this->lookupService = $lookupService;
        $this->config = $config;
        $this->logger = $logger;
        $this->requestFactory = $requestFactory;
        $this->cleansedAddressFactory = $cleansedAddressFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    public function cleanseAddress(
        AddressInterface $address,
        string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_WEBSITE
    ): ?CleansedAddressInterface {
        if (!$this->config->isAddressValidationEnabled($scopeCode, $scopeType)) {
            throw new WebapiException(
                __('Request does not match any route.'),
                0,
                WebapiException::HTTP_NOT_FOUND
            );
        }

        $request = $this->requestFactory->create();
        $request->setPostalAddress($address);

        try {
            $results = $this->lookupService->lookup($request, $scopeCode, $scopeType)
                ->getResults();
        } catch (ConfigurationException $e) {
            $this->logger->critical($e);
            throw new StateException(__('Address Cleanser is not configured properly.  Could not lookup address'), $e);
        } catch (ApiException $e) {
            $noAreas = 'No tax areas were found during the lookup.';
            if (strpos($e->getMessage(), $noAreas) === false) {
                // For all other errors
                $this->logger->error($e);
                throw $e;
            }
            // The address was SO WRONG we couldn't even try to validate it.
            $results = [];
        } catch (ValidationException $e) {
            throw new WebapiException(
                __('Invalid request. ' . $e->getMessage()),
                0,
                WebapiException::HTTP_BAD_REQUEST,
            );
        }

        if (empty($results)) {
            return null;
        }

        $result = current($results);
        $addresses = $result->getPostalAddresses();

        if (empty($addresses)) {
            return null;
        }

        /** @var AddressInterface $result */
        $result = current($addresses);

        /** @var Country $country */
        $country = $this->countryCollectionFactory->create()
            ->addCountryCodeFilter($result->getCountry(), 'iso3')
            ->load()
            ->getFirstItem();

        /** @var Region $region */
        $region = $country->getRegionCollection()
            ->addRegionCodeFilter($result->getMainDivision())
            ->load()
            ->getFirstItem();

        return $this->cleansedAddressFactory->create(
            [
                'countryName' => $country->getName(),
                'countryCode' => $country->getCountryId(),
                'postalCode' => $result->getPostalCode(),
                'regionName' => $region->getName(),
                'regionId' => $region->getId(),
                'city' => $result->getCity(),
                'streetAddress' => $result->getStreetAddress(),
                'subDivision' => $result->getSubDivision(),
            ]
        );
    }
}
