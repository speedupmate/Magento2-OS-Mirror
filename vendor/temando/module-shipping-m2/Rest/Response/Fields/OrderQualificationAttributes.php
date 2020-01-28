<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Fields;

/**
 * Temando API Order Qualification Resource Object Attributes
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderQualificationAttributes
{
    /**
     * @var \Temando\Shipping\Rest\Response\Fields\Generic\MonetaryValue[]
     */
    private $cost = [];

    /**
     * @var \Temando\Shipping\Rest\Response\Fields\OrderQualification\Description[]
     */
    private $description = [];

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\Generic\MonetaryValue[]
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\Generic\MonetaryValue[] $cost
     * @return void
     */
    public function setCost(array $cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Fields\OrderQualification\Description[]
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Fields\OrderQualification\Description[] $description
     * @return void
     */
    public function setDescription(array $description)
    {
        $this->description = $description;
    }
}
