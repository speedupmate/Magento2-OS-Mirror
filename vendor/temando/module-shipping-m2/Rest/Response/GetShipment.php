<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response;

/**
 * Temando API Get Carriers Operation
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class GetShipment implements GetShipmentInterface
{
    /**
     * @var \Temando\Shipping\Rest\Response\Type\ShipmentResponseType
     */
    private $data;

    /**
     * Obtain response entities
     *
     * @return \Temando\Shipping\Rest\Response\Type\ShipmentResponseType
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set response entities
     *
     * @param \Temando\Shipping\Rest\Response\Type\ShipmentResponseType $data
     *
     * @return void
     */
    public function setData(\Temando\Shipping\Rest\Response\Type\ShipmentResponseType $data)
    {
        $this->data = $data;
    }
}
