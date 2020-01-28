<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\AddressValidation\Model\Address;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Store\Model\ScopeInterface;
use Vertex\Data\AddressInterface as VertexAddressInterface;

interface BuilderInterface
{
    public function execute(
        AddressInterface $address,
        int $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): VertexAddressInterface;
}
