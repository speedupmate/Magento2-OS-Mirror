<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api;

use Magento\Setup\Exception;
use Magento\Store\Model\ScopeInterface;
use RuntimeException;
use Vertex\Data\ConfigurationInterface;
use Vertex\Data\ConfigurationInterfaceFactory;
use Vertex\Data\LoginInterface;
use Vertex\Data\LoginInterfaceFactory;
use Vertex\Exception\ConfigurationException;
use Vertex\Tax\Model\Config as ModuleConfig;

/**
 * Creates a {@see ConfigurationInterface} for use with the Vertex API library
 */
class ConfigBuilder
{
    /** @var ConfigurationInterfaceFactory */
    private $configFactory;

    /** @var LoginInterfaceFactory */
    private $loginFactory;

    /** @var ModuleConfig */
    private $moduleConfig;

    /** @var string|null */
    private $scopeCode;

    /** @var string */
    private $scopeType = ScopeInterface::SCOPE_STORE;

    public function __construct(
        ModuleConfig $moduleConfig,
        ConfigurationInterfaceFactory $configFactory,
        LoginInterfaceFactory $loginFactory
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->configFactory = $configFactory;
        $this->loginFactory = $loginFactory;
    }

    /**
     * Create a {@see ConfigurationInterface} object for use with the Vertex API
     *
     * @return ConfigurationInterface
     */
    public function build(): ConfigurationInterface
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->configFactory->create();

        /** @var LoginInterface $login */
        $login = $this->loginFactory->create();

        $login->setTrustedId($this->moduleConfig->getTrustedId($this->scopeCode, $this->scopeType));

        $configuration->setLogin($login);
        $configuration->setTaxAreaLookupWsdl($this->getTaxAreaLookupWsdl());
        $configuration->setTaxCalculationWsdl($this->getTaxCalculationWsdl());

        return $configuration;
    }

    /**
     * Set the Scope Code
     *
     * @param string|int|null $scopeCode
     * @return ConfigBuilder
     */
    public function setScopeCode($scopeCode): ConfigBuilder
    {
        $this->scopeCode = $scopeCode;
        return $this;
    }

    /**
     * Set the Scope Type
     *
     * @param string|null $scopeType
     * @return ConfigBuilder
     */
    public function setScopeType(?string $scopeType): ConfigBuilder
    {
        $this->scopeType = $scopeType;
        return $this;
    }

    /**
     * Assemble a URL
     *
     * @param string[] $urlParts indexed as parse_url would index them
     * @return string
     */
    private function assembleUrl(array $urlParts): string
    {
        $scheme = $urlParts['scheme'] . '://';
        $user = $urlParts['user'] ?? '';
        $pass = isset($urlParts['pass']) ? ':' . $urlParts['pass'] : '';
        $at = isset($urlParts['user']) || isset($urlParts['pass']) ? '@' : '';
        $host = $urlParts['host'];
        $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
        $path = $urlParts['path'] ?? '';
        $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        return $scheme . $user . $pass . $at . $host . $port . $path . $query . $fragment;
    }

    /**
     * Add a WSDL query parameter if one does not exist on the URL
     *
     * @param string $url
     * @return string
     */
    private function ensureWsdlQuery(string $url): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $urlParts = parse_url($url);
        $query = $urlParts['query'] ?? null;
        $wsdlFound = false;

        if ($query !== null) {
            $queryParts = explode('&', $query);
            foreach ($queryParts as $parameter) {
                $parameterParts = explode('=', $parameter);
                $name = $parameterParts[0];
                if (strtolower($name) === 'wsdl') {
                    $wsdlFound = true;
                    break;
                }
            }
        }

        if (!$wsdlFound) {
            $urlParts['query'] = $query . (empty($query) ? 'wsdl' : '&wsdl');
        }

        return $this->assembleUrl($urlParts);
    }

    /**
     * Retrieve the Tax Area Lookup WSDL URL
     *
     * @return string
     * @throws ConfigurationException
     */
    private function getTaxAreaLookupWsdl(): string
    {
        $url = $this->moduleConfig->getVertexAddressHost($this->scopeCode, $this->scopeType);
        if ($url === null) {
            throw new RuntimeException('Vertex Address WSDL Not Set');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ConfigurationException('Vertex Address WSDL Not Valid');
        }

        return $this->ensureWsdlQuery($url);
    }

    /**
     * Retrieve the Tax Calculation WSDL URL
     *
     * @return string
     * @throws ConfigurationException
     */
    private function getTaxCalculationWsdl(): string
    {
        $url = $this->moduleConfig->getVertexHost($this->scopeCode, $this->scopeType);
        if ($url === null) {
            throw new RuntimeException('Vertex Address WSDL Not Set');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ConfigurationException('Vertex Address WSDL Not Valid');
        }

        return $this->ensureWsdlQuery($url);
    }
}
