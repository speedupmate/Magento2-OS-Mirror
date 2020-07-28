<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const VERTEX_ADDRESS_API_HOST = 'tax/vertex_settings/address_api_url';
    const VERTEX_ADDRESS_SHOW_MESSAGE_ALWAYS = 'vertex_address_validation/vertex_settings/always_message';
    const VERTEX_ADDRESS_VALIDATION_ENABLE = 'vertex_address_validation/vertex_settings/enable';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var array */
    private $countryValidation;

    public function __construct(ScopeConfigInterface $scopeConfig, array $countryValidation = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->countryValidation = $countryValidation;
    }

    /**
     * Retrieve a value from the configuration within a scope
     *
     * @param string $value
     * @param null $scopeId
     * @param string $scope
     * @return string
     */
    public function getConfigValue(string $value, $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE) : string
    {
        return $this->scopeConfig->getValue($value, $scope, $scopeId);
    }

    /**
     * Returns countries to run validation on
     *
     * @return string[]
     */
    public function getCountriesToValidate() : array
    {
        return $this->countryValidation;
    }

    /**
     * Get the URL of the Tax Area Lookup API Endpoint
     *
     * @param string|null $store
     * @param string $scope
     * @return string
     */
    public function getVertexAddressHost(string $store = null, string $scope = ScopeInterface::SCOPE_STORE) : string
    {
        return (string) $this->getConfigValue(self::VERTEX_ADDRESS_API_HOST, $store, $scope);
    }

    /**
     * Check if the module is enabled
     *
     * @param null|string|int $scopeId
     * @param string $scope
     * @return bool
     */
    public function isAddressValidationEnabled($scopeId = null, string $scope = ScopeInterface::SCOPE_STORE) : bool
    {
        return $this->scopeConfig->isSetFlag(self::VERTEX_ADDRESS_VALIDATION_ENABLE, $scope, $scopeId);
    }

    /**
     * Check if we show the message all the time
     *
     * @param null|string|int $scopeId
     * @param string $scope
     * @return bool
     */
    public function showValidationSuccessMessage(
        $scopeId = null,
        string $scope = ScopeInterface::SCOPE_STORE
    ) : bool {
        return $this->scopeConfig->isSetFlag(self::VERTEX_ADDRESS_SHOW_MESSAGE_ALWAYS, $scope, $scopeId);
    }
}
