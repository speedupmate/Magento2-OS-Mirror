<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Api;

use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Store\Model\ScopeInterface;
use Vertex\AddressValidation\Model\ResourceModel\QuoteIdMask;
use Vertex\AddressValidationApi\Api\CleanseAddressInterface;
use Vertex\AddressValidationApi\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Api\Data\CleansedAddressInterface;
use Vertex\AddressValidationApi\Api\GuestCleanseAddressInterface;

class GuestAddressCleanser implements GuestCleanseAddressInterface
{
    /** @var CleanseAddressInterface */
    private $cleanser;

    /** @var QuoteIdMask */
    private $quoteMaskResource;

    public function __construct(CleanseAddressInterface $cleanser, QuoteIdMask $quoteMaskResource)
    {
        $this->cleanser = $cleanser;
        $this->quoteMaskResource = $quoteMaskResource;
    }

    public function cleanseAddress(
        string $cartId,
        AddressInterface $address,
        string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_WEBSITE
    ): ?CleansedAddressInterface {
        if (!$this->quoteMaskResource->isQuoteActive($cartId)) {
            throw new WebapiException(__('Quote not found'));
        }
        return $this->cleanser->cleanseAddress($address, $scopeCode, $scopeType);
    }
}
