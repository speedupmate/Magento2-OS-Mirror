<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Registry;

/**
 * Maintains a state of address ID and Vat Country Codes to prevent extraneous calls to the database
 */
class VatCountryCodeRegistry
{
    /** @var string[] Indexed by Address ID */
    private $registry = [];

    /**
     * Delete a vat country code from the registry
     *
     * @param string $addressId
     * @return VatCountryCodeRegistry
     */
    public function delete($addressId): VatCountryCodeRegistry
    {
        unset($this->registry[$addressId]);
        return $this;
    }

    /**
     * Retrieve a vat country code stored in the registry
     *
     * @param string $addressId
     * @return bool|string
     */
    public function get($addressId)
    {
        if (array_key_exists($addressId, $this->registry)) {
            return $this->registry[$addressId];
        }
        return false;
    }

    /**
     * Store a vat country code in the registry
     *
     * @param string $addressId
     * @param string $vatCountryCode
     * @return VatCountryCodeRegistry
     */
    public function set($addressId, $vatCountryCode)
    {
        $this->registry[$addressId] = $vatCountryCode;
        return $this;
    }
}
