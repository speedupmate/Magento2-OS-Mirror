<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\AddressValidation\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Vertex\AddressValidationApi\Api\CleanseAddressInterface;

/**
 * @deprecated Unsecured API
 * @see CleanseAddressInterface
 */
interface AddressManagementInterface
{
    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address Address data.
     * @return \Magento\Quote\Api\Data\AddressInterface
     * @api
     * @see CleanseAddressInterface::cleanseAddress()
     *
     */
    public function getValidAddress(AddressInterface $address): AddressInterface;
}
