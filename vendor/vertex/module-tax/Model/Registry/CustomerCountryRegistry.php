<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Registry;

/**
 * Maintains a state of customer ID and customer countries to prevent extraneous calls to the database
 */
class CustomerCountryRegistry
{
    /** @var string[] Indexed by Customer ID */
    private $registry = [];

    /**
     * Delete a customer country from the registry
     *
     * @param string $customerId
     * @return CustomerCountryRegistry
     */
    public function delete($customerId)
    {
        unset($this->registry[$customerId]);
        return $this;
    }

    /**
     * Retrieve a customer country stored in the registry
     *
     * @param string $customerId
     * @return bool|string
     */
    public function get($customerId)
    {
        if (array_key_exists($customerId, $this->registry)) {
            return $this->registry[$customerId];
        }
        return false;
    }

    /**
     * Store a customer country in the registry
     *
     * @param string $customerId
     * @param string $customerCountry
     * @return CustomerCountryRegistry
     */
    public function set($customerId, $customerCountry)
    {
        $this->registry[$customerId] = $customerCountry;
        return $this;
    }
}
