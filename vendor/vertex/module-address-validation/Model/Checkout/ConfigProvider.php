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
    const SHOW_VALIDATION_SUCCESS_MESSAGE = 'showValidationSuccessMessage';
    const COUNTRY_VALIDATION_IDS = 'countryValidation';

    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig() : array
    {
        return [
            self::VERTEX_ADDRESS_VALIDATION_CONFIG => [
                self::IS_ADDRESS_VALIDATION_ENABLED   => $this->config->isAddressValidationEnabled(),
                self::SHOW_VALIDATION_SUCCESS_MESSAGE => $this->config->showValidationSuccessMessage(),
                self::COUNTRY_VALIDATION_IDS          => $this->config->getCountriesToValidate()
            ]
        ];
    }
}
