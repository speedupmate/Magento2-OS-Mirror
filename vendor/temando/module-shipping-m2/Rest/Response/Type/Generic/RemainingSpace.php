<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Type\Generic;

/**
 * Temando API Remaining Space Response Type
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class RemainingSpace
{
    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Value
     */
    private $volume;

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Value
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Value $volume
     * @return void
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }
}
