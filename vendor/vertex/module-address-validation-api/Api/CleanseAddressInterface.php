<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidationApi\Api;

use Magento\Store\Model\ScopeInterface;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface;

/**
 * @api
 */
interface CleanseAddressInterface
{
    /**
     * Query Vertex to cleanse an address
     *
     * @param \Vertex\AddressValidationApi\Api\Data\AddressInterface $address
     * @param string $scopeCode
     * @param string $scopeType
     * @return \Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface|null A cleansed address.  NULL if Vertex
     *   could not determine a cleansed address
     * @throws \Magento\Framework\Webapi\Exception
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Vertex\Exception\ApiException
     * @throws \Vertex\Exception\ValidationException
     */
    public function cleanseAddress(
        AddressInterface $address,
        string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_WEBSITE
    ): ?CleansedAddressInterface;
}
