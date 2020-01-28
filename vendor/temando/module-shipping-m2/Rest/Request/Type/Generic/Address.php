<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type\Generic;

use Temando\Shipping\Rest\Request\Type\EmptyFilterableInterface;
use Temando\Shipping\Rest\Request\Type\AttributeFilter;

/**
 * Temando API Address
 *
 * @package Temando\Shipping\Rest
 * @author  Jason Jewel <jason.jewel@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Address implements \JsonSerializable, EmptyFilterableInterface
{
    /**
     * @var string
     */
    private $countryCode;

    /**
     * Address constructor.
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'countryCode' => $this->countryCode,
        ];
    }

    /**
     * Check if any properties are set.
     *
     * @return bool
     */
    public function isEmpty()
    {
        $properties = get_object_vars($this);
        $properties = AttributeFilter::notEmpty($properties);
        return empty($properties);
    }
}
