<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\AddressValidation\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Vertex\AddressValidation\Model\Config;

class ConfigProvider implements ConfigProviderInterface
{
    const VERTEX_ADDRESS_VALIDATION_CONFIG = 'vertexAddressValidationConfig';
    const IS_ADDRESS_VALIDATION_ENABLED = 'isAddressValidationEnabled';
    const IS_ALWAYS_SHOWING_THE_MESSAGE = 'isAlwaysShowingTheMessage';
    const COUNTRY_VALIDATION_IDS = 'countryValidation';

    /** @var Config */
    private $config;

    /** @var array */
    private $countryValidation;

    public function __construct(
        Config $config,
        array $countryValidation = []
    ) {
        $this->config = $config;
        $this->countryValidation = $countryValidation;
    }

    public function getConfig() : array
    {
        return [
            self::VERTEX_ADDRESS_VALIDATION_CONFIG => [
                self::IS_ADDRESS_VALIDATION_ENABLED => $this->config->isAddressValidationEnabled(),
                self::IS_ALWAYS_SHOWING_THE_MESSAGE => $this->config->isAlwaysShowingTheMessage(),
                self::COUNTRY_VALIDATION_IDS        => $this->countryValidation
            ]
        ];
    }
}
