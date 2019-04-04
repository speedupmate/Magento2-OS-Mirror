<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Qualification;

/**
 * Temando API Qualification Geo Address Request Type
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class GeoAddress implements \JsonSerializable
{
    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * GeoAddress constructor.
     * @param string $postalCode
     * @param string $countryCode
     */
    public function __construct($postalCode, $countryCode)
    {
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        $geoAddress = [
            'type' => 'geoaddress',
            'attributes' => [
                'postalCode' => $this->postalCode,
                'countryCode' => $this->countryCode,
            ],
        ];

        return $geoAddress;
    }
}
