<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\AddressValidation\Api;

use Magento\Quote\Api\Data\AddressInterface;

interface AddressManagementInterface
{
    /**
     * @api
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address Address data.
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getValidAddress(AddressInterface $address): AddressInterface;
}
