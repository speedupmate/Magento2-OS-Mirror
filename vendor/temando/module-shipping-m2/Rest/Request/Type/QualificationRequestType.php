<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request\Type;

use Temando\Shipping\Rest\Request\Type\Qualification\GeoAddress;
use Temando\Shipping\Rest\Request\Type\Qualification\Order;

/**
 * QualificationRequestType
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualificationRequestType implements \JsonSerializable
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var GeoAddress
     */
    private $geoAddress;

    /**
     * QualificationRequestType constructor.
     * @param Order $order
     * @param GeoAddress|null $geoAddress
     */
    public function __construct(
        Order $order,
        GeoAddress $geoAddress = null
    ) {
        $this->order = $order;
        $this->geoAddress = $geoAddress;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        $requestData = [
            'data' => [
                $this->order,
                $this->geoAddress,
            ]
        ];

        $requestData = AttributeFilter::notEmpty($requestData);

        return $requestData;
    }
}
