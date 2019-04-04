<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Document;

/**
 * Temando API Qualify Order Document
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class QualifyOrder implements QualifyOrderInterface
{
    /**
     * @var \Temando\Shipping\Rest\Response\DataObject\OrderQualification[]
     */
    private $data = [];

    /**
     * @var \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[]
     */
    private $included = [];

    /**
     * Obtain response entity
     *
     * @return \Temando\Shipping\Rest\Response\DataObject\OrderQualification[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set response entity
     *
     * @param \Temando\Shipping\Rest\Response\DataObject\OrderQualification[] $experiences
     * @return void
     */
    public function setData(array $experiences)
    {
        $this->data = $experiences;
    }

    /**
     * Obtain included collection points.
     *
     * @return \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[]
     */
    public function getIncluded()
    {
        return $this->included;
    }

    /**
     * Set included collection points.
     *
     * @param \Temando\Shipping\Rest\Response\DataObject\CollectionPoint[] $included
     *
     * @return void
     */
    public function setIncluded(array $included)
    {
        $this->included = $included;
    }
}
